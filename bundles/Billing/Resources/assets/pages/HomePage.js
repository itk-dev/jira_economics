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
import Select from 'react-select';

class HomePage extends Component {
    constructor (props) {
        super(props);

        this.state = {
            filterValues: {
                creationDateSorting: 'asc',
                creatorFilter: ''
            },
            allInvoices: {},
            showModal: false,
            invoiceIdToDelete: null,
            selectedItems: {}
        };

        this.handleFilterChange.bind(this);
    };

    componentDidMount () {
        const { dispatch } = this.props;

        dispatch(rest.actions.getAllInvoices())
            .then((response) => {
                this.setState({ allInvoices: response });
            })
            .catch((reason) => console.log('isCanceled', reason));
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

    handleInvoiceDelete = (invoiceId) => {
        this.setState({ invoiceIdToDelete: invoiceId, showModal: true });
    };

    removeInvoiceFromState (invoiceId) {
        let filteredInvoices = this.state.allInvoices.data.filter((invoice) => {
            return invoiceId !== invoice.id;
        });
        let remainingInvoices = { 'data': filteredInvoices };
        this.setState({ allInvoices: remainingInvoices });
    };

    exportInvoices = () => {
        const items = Object.keys(this.state.selectedItems).reduce((carry, key) => {
            if (
                carry.indexOf(key) === -1 &&
                this.state.selectedItems.hasOwnProperty(key) &&
                this.state.selectedItems[key]
            ) {
                carry.push(key);
            }

            return carry;
        }, []);

        window.open(`/jira/billing/jira_api/export_invoices?` + jQuery.param({ ids: items }), '_blank');
    };

    toggleItem = (itemId) => {
        this.setState((prevState) => ({
            selectedItems: {
                ...prevState.selectedItems,
                [itemId]: !prevState.selectedItems[itemId]
            }
        }));
    };

    handleFilterChange = (field, value) => {
        this.setState((prevState) => ({
            filterValues: {
                ...prevState.filterValues,
                [field]: value
            }
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

        let sortOrder = this.state.filterValues.creationDateSorting;
        let invoices = [].concat(this.state.allInvoices.data)
            .sort((i1, i2) => {
                if (sortOrder === 'asc') {
                    return i1.created > i2.created ? 1 : -1;
                } else {
                    return i1.created < i2.created ? 1 : -1;
                }
            });

        if (this.state.filterValues.creatorFilter !== '') {
            invoices = invoices.filter(item => item.created_by === this.state.filterValues.creatorFilter);
        }

        let creators = invoices.reduce((carry, item) => {
            if (item.created_by !== null && carry.indexOf(item.created_by) === -1) {
                carry.push(item.created_by);
            }
            return carry;
        }, []);

        const creatorFilterOptions = creators.map((creator) => {
            return {
                value: creator,
                label: creator
            };
        });

        const creationDateSortingOptions = [
            {
                value: 'desc',
                label: t('home_page.sorting.newest')
            },
            {
                value: 'asc',
                label: t('home_page.sorting.oldest')
            }
        ];

        const tabs = [
            {
                title: t('home_page.tab.not_recorded'),
                keyEvent: 'drafts',
                items: invoices.filter(item => !item.recorded),
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
                                href={'/jira/billing/project/' + item.projectId + '/' + item.id}>
                                <i className="fas fa-edit mx-2" />
                                <span className="sr-only">{t('home_page.sr_only.edit_invoice')}</span>
                            </Button>
                        </OverlayTrigger>
                        <OverlayTrigger
                            key="show_export_invoice"
                            placement="top"
                            overlay={
                                <Tooltip id="tooltip-show_export_invoice">
                                    {t('home_page.tooltip.show_export_invoice')}
                                </Tooltip>
                            }
                        >
                            <Button
                                className="btn-secondary"
                                href={'/jira/billing/show_export_invoice/' + item.id}>
                                <i className="fas fa-list-alt mx-2" />
                                <span className="sr-only">{t('home_page.sr_only.show_export_invoice')}</span>
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
                                onClick={() => { this.handleInvoiceDelete(item.id); }}>
                                <i className="fas fa-trash-alt mx-2" />
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
                items: invoices.filter(item => item.recorded),
                invoiceActions: (
                    <ButtonGroup
                        className="btn-group-sm float-right"
                        aria-label="Invoice functions">
                        <Button onClick={this.exportInvoices.bind(this)}>
                            Eksportér fakturaer til CSV
                        </Button>
                    </ButtonGroup>
                ),
                actions: () => (
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
                                <i className="fas fa-file-csv mx-2" />
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
                                    <label htmlFor={'creationDateSorting'}>{t('home_page.sort.created_at')}</label>
                                    <Select
                                        id={'creationDateSorting'}
                                        value={creationDateSortingOptions.filter(item => this.state.filterValues.creationDateSorting === item.value)}
                                        name={'creationDateSorting'}
                                        aria-label={t('home_page.filter.creator')}
                                        onChange={(selectedOption) => this.handleFilterChange('creationDateSorting', selectedOption ? selectedOption.value : '')}
                                        options={creationDateSortingOptions}
                                    />
                                    <label htmlFor={'creatorFilter'}>{t('home_page.filter.creator')}</label>
                                    <Select
                                        id={'creatorFilter'}
                                        value={creatorFilterOptions.filter(item => this.state.filterValues.creatorFilter === item.value)}
                                        name={'creatorFilter'}
                                        isSearchable={true}
                                        isClearable={true}
                                        aria-label={t('home_page.filter.creator')}
                                        placeholder={t('home_page.filter.creator_option.all')}
                                        onChange={(selectedOption) => this.handleFilterChange('creatorFilter', selectedOption ? selectedOption.value : '')}
                                        options={creatorFilterOptions}
                                    />
                                </Form.Group>
                            </Form>
                            <Table responsive striped hover borderless>
                                <thead>
                                    <tr>
                                        {tab.keyEvent === 'posted' &&
                                            <th> </th>
                                        }
                                        <th>{t('home_page.table.invoice')}</th>
                                        <th>{t('home_page.table.project')}</th>
                                        <th>{t('home_page.table.creator')}</th>
                                        <th>{t('home_page.table.date')}</th>
                                        <th>{t('home_page.table.amount')}</th>
                                        {tab.keyEvent === 'posted' &&
                                            <th>{t('home_page.table.exported_date')}</th>
                                        }
                                        <th className="text-right">{t('home_page.table.functions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tab.items.map((item) => (
                                        <tr key={item.id}>
                                            {tab.keyEvent === 'posted' &&
                                                <td>
                                                    <input
                                                        name={'item-toggle-' + item.id}
                                                        type="checkbox"
                                                        value={ this.state.selectedItems[item.id] }
                                                        onChange={ () => { this.toggleItem(item.id); } }
                                                    />
                                                </td>
                                            }
                                            <td>
                                                <a href={'/jira/billing/project/' + item.projectId + '/' + item.id}>
                                                    <strong>{item.name}</strong>
                                                </a>
                                            </td>
                                            <td>{item.projectName}</td>
                                            <td>{item.created_by}</td>
                                            <td>
                                                <Moment format="DD-MM-YYYY">{item.created}</Moment>
                                            </td>
                                            <td>
                                                <strong>{item.totalPrice}</strong>
                                            </td>
                                            {tab.keyEvent === 'posted' &&
                                                <td>
                                                    {item.exportedDate &&
                                                        <Moment format="DD-MM-YYYY">{item.exportedDate}</Moment>
                                                    }
                                                </td>
                                            }
                                            <td className="text-right">
                                                {tab.actions(item)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </Table>

                            {tab.invoiceActions}
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
        );
    }
}

HomePage.propTypes = {
    allInvoices: PropTypes.object,
    dispatch: PropTypes.func.isRequired,
    history: PropTypes.shape({
        push: PropTypes.func.isRequired
    }).isRequired,
    t: PropTypes.func.isRequired
};

const mapStateToProps = state => {
    return {
        allInvoices: state.allInvoices
    };
};

export default connect(
    mapStateToProps
)(withTranslation()(HomePage));
