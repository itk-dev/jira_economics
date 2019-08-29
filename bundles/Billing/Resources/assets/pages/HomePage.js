import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import PageTitle from '../components/PageTitle';
import PropTypes from 'prop-types';
import Moment from 'react-moment';
import 'moment-timezone';
import rest from '../redux/utils/rest';
import ContentWrapper from '../components/ContentWrapper';
import Tabs from 'react-bootstrap/Tabs';
import Tab from 'react-bootstrap/Tab';
import Table from 'react-bootstrap/Table';
import Form from 'react-bootstrap/Form';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Button from 'react-bootstrap/Button';
import OverlayTrigger from 'react-bootstrap/OverlayTrigger';
import Tooltip from 'react-bootstrap/Tooltip';
import Spinner from '../components/Spinner';
import { withTranslation } from 'react-i18next';
import ConfirmModal from '../components/ConfirmModal';

class HomePage extends Component {
    constructor (props) {
        super(props);

        this.state = {
            allInvoices: {},
            allInvoiceEntries: {},
            showModal: false,
            invoiceIdToDelete: null,
            sortOrder: 'asc'
        };
    };

    componentDidMount () {
        const { dispatch } = this.props;

        dispatch(rest.actions.getAllInvoices())
            .then((response) => {
                this.setState({ allInvoices: response });
            })
            .catch((reason) => console.log('isCanceled', reason.isCanceled));

        dispatch(rest.actions.getAllInvoiceEntries())
            .then((response) => {
                this.setState({ allInvoiceEntries: response });
            })
            .catch((reason) => console.log('isCanceled', reason.isCanceled));
    };

    handleInvoiceDeleteConfirm = (event) => {
        event.preventDefault();
        const invoiceId = this.state.invoiceIdToDelete;

        const { dispatch } = this.props;
        dispatch(rest.actions.deleteInvoice({ id: invoiceId }))
            .then(() => {
                this.removeInvoiceFromState(invoiceId);
            })
            .catch((reason) => console.log('isCanceled', reason.isCanceled));

        this.setState({ showModal: false, invoiceIdToDelete: null });
    };

    handleInvoiceDelete = (event, invoiceId) => {
        event.preventDefault();
        this.setState({ invoiceIdToDelete: invoiceId, showModal: true });
    };

    removeInvoiceFromState (invoiceId) {
        let filteredInvoices = this.state.allInvoices.data.filter((invoice) => {
            return invoiceId !== invoice.invoiceId;
        });
        let remainingInvoices = { 'data': filteredInvoices };
        this.setState({ allInvoices: remainingInvoices });
    };

    getPriceForInvoice (invoiceId) {
        if (!this.state.allInvoiceEntries.data) {
            return 0;
        }
        let invoiceEntries = this.state.allInvoiceEntries.data.filter((invoiceEntry) => {
            return invoiceEntry.invoiceId === invoiceId;
        });
        if (invoiceEntries === undefined) {
            return 'N/A';
        }
        let totalPrice = 0;
        invoiceEntries.forEach(invoiceEntry => {
            totalPrice += invoiceEntry.price;
        });
        return totalPrice.toFixed(2);
    };

    toggleSort () {
        this.setState((prevState) => ({
            sortOrder: prevState.sortOrder === 'asc' ? 'desc' : 'asc'
        }));
    };

    render () {
        const { t } = this.props;

        if (!this.state.allInvoices.data || this.state.allInvoices.loading) {
            return (
                <ContentWrapper>
                    <Spinner/>
                </ContentWrapper>
            );
        }

        let sortOrder = this.state.sortOrder;
        let invoices = [].concat(this.state.allInvoices.data)
            .sort((i1, i2) => {
                if (sortOrder === 'asc') {
                    return i1.created.date > i2.created.date ? 1 : -1;
                } else {
                    return i1.created.date < i2.created.date ? 1 : -1;
                }
            });

        const tabs = [
            {
                title: t('home_page.tab.not_recorded'),
                keyEvent: 'drafts',
                items: invoices
                    .filter((item) => {
                        return item.recorded === false;
                    }),
                actions: (item) => (
                    <ButtonGroup size="sm"
                        className="float-right"
                        aria-label="Invoice functions">
                        <OverlayTrigger
                            key="edit"
                            placement="top"
                            overlay={
                                <Tooltip id="tooltip-edit">
                                    {t('home_page.tooltip.edit_invoice')}
                                </Tooltip>
                            }
                        >
                            <Button
                                className="btn-primary"
                                href={'/jira/billing/project/' + item.jiraProjectId + '/' + item.invoiceId}>
                                <i className="fas fa-edit mx-2"></i>
                                <span className="sr-only">{t('home_page.sr_only.edit_invoice')}</span>
                            </Button>
                        </OverlayTrigger>
                        <OverlayTrigger
                            key="delete"
                            placement="top"
                            overlay={
                                <Tooltip
                                    id="tooltip-delete">
                                    {t('home_page.tooltip.delete_invoice')}
                                </Tooltip>
                            }
                        >
                            <Button
                                className="btn-danger"
                                onClick={this.handleInvoiceDelete.bind(this, item.invoiceId)}>
                                <i className="fas fa-trash-alt mx-2"></i>
                                <span
                                    className="sr-only">{t('home_page.sr_only.delete_invoice')}</span>
                            </Button>
                        </OverlayTrigger>
                    </ButtonGroup>
                )
            },
            {
                title: t('home_page.tab.recorded'),
                keyEvent: 'posted',
                items: invoices
                    .filter((item) => {
                        return item.recorded === true;
                    }),
                actions: (item) => (
                    <ButtonGroup
                        className="btn-group-sm float-right"
                        aria-label="Invoice functions">
                        <OverlayTrigger
                            key="download-csv"
                            placement="top"
                            overlay={
                                <Tooltip
                                    id="tooltip-download-csv">
                                    {t('home_page.tooltip.download_csv')}
                                </Tooltip>
                            }
                        >
                            <Button>
                                <i className="fas fa-file-csv mx-2"></i>
                                <span
                                    className="sr-only">{t('home_page.sr_only.download_csv')}</span>
                            </Button>
                        </OverlayTrigger>
                    </ButtonGroup>
                )
            }
        ];

        return (
            <ContentWrapper>
                <PageTitle breadcrumb="">{t('home_page.invoices')}</PageTitle>
                <Tabs defaultActiveKey="drafts"
                    id="uncontrolled-tab-example">
                    {tabs && tabs.map((tab, index) => (
                        <Tab key={index} eventKey={tab.keyEvent} title={tab.title}>
                            <Form className="mt-3 mb-1 w-25">
                                <Form.Group className="mb-0">
                                    <Form.Label className="sr-only">{t('home_page.sort')}</Form.Label>
                                    <Form.Control size="sm" as="select" value={this.state.sortOrder} onChange={this.toggleSort.bind(this) }>
                                        <option value={'desc'}>{t('home_page.sorting.newest')}</option>
                                        <option value={'asc'}>{t('home_page.sorting.oldest')}</option>
                                    </Form.Control>
                                </Form.Group>
                            </Form>
                            <Table responsive striped hover borderless>
                                <thead>
                                    <tr>
                                        <th>{t('home_page.table.invoice')}</th>
                                        <th>{t('home_page.table.project')}</th>
                                        <th>{t('home_page.table.date')}</th>
                                        <th>{t('home_page.table.amount')}</th>
                                        <th className="text-right">{t('home_page.table.functions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tab.items.map((item) => (
                                        <tr key={item.invoiceId}>
                                            <td><a
                                                href={'/jira/billing/project/' + item.jiraProjectId + '/' + item.invoiceId}><strong>{item.invoiceName}</strong></a>
                                            </td>
                                            <td>{item.jiraProjectName}</td>
                                            <td><Moment
                                                format="DD-MM-YYYY">{item.created.date}</Moment>
                                            </td>
                                            <td>
                                                <strong>{this.getPriceForInvoice(item.invoiceId)}</strong>
                                            </td>
                                            <td className="text-right">
                                                {tab.actions(item)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </Table>
                        </Tab>
                    ))}
                </Tabs>
                <ConfirmModal
                    showModal={this.state.showModal}
                    variant={'danger'}
                    title={t('home_page.modal.title')}
                    cancelText={t('common.modal.cancel')}
                    confirmText={t('common.modal.confirm')}
                    body={
                        <div>{t('home_page.modal.body')}</div>
                    }
                    onHide={() => { this.setState({ showModal: false }); }}
                    onCancel={() => { this.setState({ showModal: false }); }}
                    onConfirm={ this.handleInvoiceDeleteConfirm.bind(this) }
                />
            </ContentWrapper>
        )
    }
}

HomePage.propTypes = {
    allInvoices: PropTypes.object,
    allInvoiceEntries: PropTypes.object,
    dispatch: PropTypes.func.isRequired,
    t: PropTypes.func.isRequired
};

const mapStateToProps = state => {
    return {
        allInvoices: state.allInvoices,
        allInvoiceEntries: state.allInvoiceEntries
    };
};

export default connect(
    mapStateToProps
)(withTranslation()(HomePage));
