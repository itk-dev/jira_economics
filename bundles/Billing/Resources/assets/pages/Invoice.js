import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import PropTypes from 'prop-types';
import Moment from 'react-moment';
import 'moment-timezone';
import rest from '../redux/utils/rest';
import ContentFooter from '../components/ContentFooter';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Table from 'react-bootstrap/Table';
import ListGroup from 'react-bootstrap/ListGroup';
import Spinner from '../components/Spinner';
import nl2br from 'react-nl2br';
import OverlayTrigger from 'react-bootstrap/OverlayTrigger';
import Tooltip from 'react-bootstrap/Tooltip';
import ConfirmModal from '../components/ConfirmModal';
import { withTranslation, Trans } from 'react-i18next';

class Invoice extends Component {
    constructor (props) {
        super(props);

        this.handleInvoiceEntryDelete = this.handleInvoiceEntryDelete.bind(this);

        this.state = {
            showDeleteModal: false,
            showRecordModal: false,
            showDeleteEntryModal: false,
            editDescription: false,

            invoice: null,
            invoiceEntries: {},
            entryIdToDelete: null
        };
    };

    componentDidMount () {
        const { dispatch } = this.props;
        dispatch(rest.actions.getProject({ id: `${this.props.match.params.projectId}` }))
            .then((response) => {
                this.setState({ project: response });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getInvoice({ id: `${this.props.match.params.invoiceId}` }))
            .then((response) => {
                this.setState({ invoice: response });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getInvoiceEntries({ id: `${this.props.match.params.invoiceId}` }))
            .then((response) => {
                this.setState({ invoiceEntries: response });
            })
            .catch((reason) => console.log('isCanceled', reason));
    };

    createEntry = (isJiraEntry) => {
        const { dispatch } = this.props;

        let invoiceId = this.props.match.params.invoiceId;

        let invoiceEntryData = {
            invoiceId,
            isJiraEntry
        };

        dispatch(rest.actions.createInvoiceEntry({}, {
            body: JSON.stringify(invoiceEntryData)
        }))
            .then((response) => {
                this.props.history.push(`/project/${this.props.match.params.projectId}/${response.invoiceId}/${response.id}`);
            })
            .catch((reason) => {
                // @TODO: Warn about error.
                console.log('isCanceled', reason);
            });
    };

    handleAddFromJira = (event) => {
        event.preventDefault();
        this.createEntry(true);
    };

    handleAddManually = (event) => {
        event.preventDefault();
        this.createEntry(false);
    };

    handleDeleteAccept = (event) => {
        event.preventDefault();

        this.setState({ showDeleteModal: false });

        const { dispatch } = this.props;
        dispatch(rest.actions.deleteInvoice({ id: `${this.props.match.params.invoiceId}` }))
            .then((response) => {
                this.props.history.push(`/`);
            })
            .catch((reason) => {
                console.log(reason);
            });
    };

    handleRecordAccept = (event) => {
        event.preventDefault();

        this.setState({ showRecordModal: false });

        const { dispatch } = this.props;
        const id = this.props.match.params.invoiceId;
        const name = this.props.invoice.data.name;
        const recorded = true;
        const invoiceData = {
            id,
            name,
            recorded
        };
        dispatch(rest.actions.updateInvoice({ id: `${this.props.match.params.invoiceId}` }, {
            body: JSON.stringify(invoiceData)
        }));

        // @TODO: Handle situation after invoice has been recorded.
    };

    handleSaveEditDescription = (event) => {
        event.preventDefault();

        if (!this.state.invoice.id) {
            return;
        }

        let fieldName = event.target.name;
        let fieldVal = event.target.value;
        if (fieldName === 'description' && fieldVal !== this.state.invoice.description) {
            this.setState({
                ...this.state,
                invoice: {
                    ...this.state.invoice,
                    description: fieldVal
                }
            });

            let data = {
                id: this.state.invoice.id,
                description: fieldVal
            };

            const { dispatch } = this.props;
            dispatch(rest.actions.updateInvoice({ id: this.state.invoice.id }, {
                body: JSON.stringify(data)
            }));
        }

        this.setState({
            editDescription: false
        });
    };

    handleInvoiceEntryDelete = (event) => {
        event.preventDefault();
        const { dispatch } = this.props;
        dispatch(rest.actions.deleteInvoiceEntry({ id: `${this.state.entryIdToDelete}` }))
            .then((response) => {
                this.setState({
                    entryIdToDelete: null,
                    showDeleteEntryModal: false
                });

                dispatch(rest.actions.getInvoiceEntries({ id: `${this.props.match.params.invoiceId}` }))
                    .then((response) => {
                        this.setState({ invoiceEntries: response });
                    })
                    .catch((reason) => console.log('isCanceled', reason));
            })
            .catch((reason) => {
                console.log(reason);
            });
    };

    render () {
        const { t } = this.props;
        const invoiceId = this.props.match.params.invoiceId;
        const invoiceRecorded = this.props.invoice.data.recorded ? t('invoice.recorded_true') : t('invoice.recorded_false');

        if (this.props.invoice.data.jiraId && this.props.invoice.data.jiraId !== parseInt(this.props.match.params.projectId)) {
            return (
                <ContentWrapper>
                    <PageTitle>Invoice</PageTitle>
                    <div>Error: the requested invoice does not match the project specified in the URL</div>
                    <div>(URL contains projectId {this.props.match.params.projectId}
                        but invoice with id {this.props.match.params.invoiceId}
                        belongs to project with id {this.props.invoice.data.jiraId})
                    </div>
                </ContentWrapper>
            );
        } else if (this.props.project.data.name && this.props.invoiceEntries.data) {
            return (
                <ContentWrapper>
                    <PageTitle
                        breadcrumb={'Invoice for project [' + this.props.project.data.name + '] (' + this.props.match.params.projectId + ')'}>
                        {this.props.invoice.data.name && this.props.invoice.data.name}
                    </PageTitle>
                    <div className="row mb-3">
                        <div className="col-md-6">
                            <p>

                                <Trans i18nKey="invoice.invoice_id">
                                    Invoice number: <strong className="pr-3">{{ invoiceId }}</strong>
                                </Trans>
                                <Trans i18nKey="invoice.invoice_recorded">
                                    Invoice recorded: <strong>{{ invoiceRecorded }}</strong>
                                </Trans>
                            </p>
                            {this.props.invoice.loading &&
                                <Spinner/>
                            }
                            {!this.state.editDescription && !this.props.invoice.loading &&
                                <div onClick={() => { this.setState({ editDescription: true }); }} className={'mb-3'}>
                                    {nl2br(this.props.invoice.data.description)}
                                </div>
                            }
                            {this.state.editDescription && !this.props.invoice.loading &&
                                <Form onBlur={this.handleSaveEditDescription.bind(this)}>
                                    <Form.Group>
                                        <Form.Control
                                            id="invoice-description"
                                            name={'description'}
                                            className={'mb-3 border-0'}
                                            as="textarea" rows="5"
                                            defaultValue={this.props.invoice.data.description}
                                            placeholder="Enter description for invoice here. Leave textarea to save.">
                                        </Form.Control>
                                    </Form.Group>
                                </Form>
                            }
                        </div>
                        <div className="col-md-6">
                            <h4>{t('invoice.client_information')}</h4>
                            {
                                this.props.invoice.data.account &&
                                <ListGroup>
                                    <ListGroup.Item>
                                        <span className="text-muted d-inline-block w-25">
                                            {t('invoice.client_name')}
                                        </span>{this.props.invoice.data.account.name}
                                    </ListGroup.Item>
                                    <ListGroup.Item>
                                        <span className="text-muted d-inline-block w-25">
                                            {t('invoice.client_contact')}
                                        </span>{this.props.invoice.data.account.contact.name}
                                    </ListGroup.Item>
                                    <ListGroup.Item>
                                        <span className="text-muted d-inline-block w-25">
                                            {t('invoice.client_default_price')}
                                        </span>{this.props.invoice.data.account.defaultPrice}
                                    </ListGroup.Item>
                                </ListGroup>
                            }
                        </div>
                    </div>
                    {!this.props.invoice.data.recorded &&
                        <div className="row mb-3">
                            <div className="col-md-12 text-right">
                                <ButtonGroup aria-label="Invoice actions">
                                    <Button variant="primary" type="submit"
                                        id="record-invoice"
                                        onClick={() => { this.setState({ showRecordModal: true }); }}>
                                        {t('invoice.record_invoice')}
                                    </Button>
                                    <Button variant="danger" type="submit"
                                        id="delete" className="mr-3"
                                        onClick={() => { this.setState({ showDeleteModal: true }); }}>
                                        {t('invoice.delete_invoice')}
                                    </Button>
                                </ButtonGroup>
                            </div>
                        </div>
                    }
                    <div className="row">
                        <div className="col-md-12">
                            <h2>{t('invoice.invoice_entries_list_title')}</h2>
                            <div className="row mb-3">
                                <div className="col-md-12">
                                    <Button variant="outline-success"
                                        type="submit" className="mr-3"
                                        onClick={this.handleAddFromJira}>{t('invoice.add_new_jira_entry')}</Button>
                                    <Button variant="outline-success"
                                        type="submit"
                                        onClick={this.handleAddManually}>{t('invoice.add_new_manual_entry')}</Button>
                                </div>
                            </div>
                            {this.props.invoiceEntries.loading &&
                                <Spinner/>
                            }
                            {!this.props.invoiceEntries.loading &&
                                <Table responsive hover className="table-borderless bg-light">
                                    <thead>
                                        <tr>
                                            <th>{t('invoice.form.to_account')}</th>
                                            <th>{t('invoice.form.product')}</th>
                                            <th>{t('invoice.form.description')}</th>
                                            <th>{t('invoice.form.amount')}</th>
                                            <th>{t('invoice.form.price')}</th>
                                            <th>{t('invoice.form.total_price')}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {this.state.invoiceEntries.data && this.state.invoiceEntries.data.map((item) =>
                                            <tr key={item.id}>
                                                <td>{item.account}</td>
                                                <td>{item.product}</td>
                                                <td>{item.description}</td>
                                                <td>{item.amount}</td>
                                                <td>{item.price}</td>
                                                <td>{item.amount * item.price}</td>
                                                <td className="text-right">
                                                    <ButtonGroup size="sm" className="float-right" aria-label="Invoice entry functions">
                                                        <OverlayTrigger key="edit" placement="top"
                                                            overlay={
                                                                <Tooltip
                                                                    id="tooltip-edit">
                                                                    {t('invoice.edit_entry')}
                                                                </Tooltip>
                                                            }
                                                        >
                                                            <Button
                                                                className="btn-primary"
                                                                href={'/jira/billing/project/' + this.props.match.params.projectId + '/' + this.props.match.params.invoiceId + '/' + item.id}>
                                                                <i className="fas fa-edit mx-2"></i>
                                                                <span className="sr-only">{t('common.edit')}</span>
                                                            </Button>
                                                        </OverlayTrigger>
                                                        <OverlayTrigger
                                                            key="delete"
                                                            placement="top"
                                                            overlay={
                                                                <Tooltip
                                                                    id="tooltip-delete">
                                                                    {t('invoice.delete_entry')}
                                                                </Tooltip>
                                                            }
                                                        >
                                                            <Button
                                                                className="btn-danger"
                                                                onClick={() => {
                                                                    this.setState({
                                                                        showDeleteEntryModal: true,
                                                                        entryIdToDelete: item.id
                                                                    });
                                                                }}>
                                                                <i className="fas fa-trash-alt mx-2"></i>
                                                                <span
                                                                    className="sr-only">{t('common.delete')}</span>
                                                            </Button>
                                                        </OverlayTrigger>
                                                    </ButtonGroup>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </Table>
                            }
                        </div>
                    </div>
                    <ContentFooter>
                        Invoice created <strong>
                            <Moment format="YYYY-MM-DD HH:mm">
                                {this.props.invoice.data.created}
                            </Moment>
                        </strong>
                    </ContentFooter>

                    {/* Confirm delete entry modal */}
                    <ConfirmModal
                        showModal={this.state.showDeleteEntryModal}
                        variant={'danger'}
                        title={t('invoice.modals.delete_entry.title')}
                        body={
                            <div>{t('invoice.modals.delete_entry.body')}</div>
                        }
                        cancelText={t('common.modal.cancel')}
                        confirmText={t('common.modal.confirm')}
                        onHide={() => {}}
                        onCancel={() => { this.setState({ showDeleteEntryModal: false, entryToDelete: null }); } }
                        onConfirm={ this.handleInvoiceEntryDelete.bind(this) }
                    />

                    {/* Confirm delete modal */}
                    <ConfirmModal
                        showModal={this.state.showDeleteModal}
                        variant={'danger'}
                        title={t('invoice.modals.delete.title')}
                        body={
                            <div>{t('invoice.modals.delete.body')}</div>
                        }
                        cancelText={t('common.modal.cancel')}
                        confirmText={t('common.modal.confirm')}
                        onHide={() => {}}
                        onCancel={() => { this.setState({ showDeleteModal: false }); } }
                        onConfirm={this.handleDeleteAccept.bind(this)}
                    />

                    {/* Confirm record modal */}
                    <ConfirmModal
                        showModal={this.state.showRecordModal}
                        variant={'warning'}
                        title={t('invoice.modals.record.title')}
                        body={
                            <div>{t('invoice.modals.record.body')}</div>
                        }
                        cancelText={t('common.modal.cancel')}
                        confirmText={t('common.modal.confirm')}
                        onHide={() => {}}
                        onCancel={() => { this.setState({ showRecordModal: false }); } }
                        onConfirm={this.handleRecordAccept.bind(this)}
                    />
                </ContentWrapper>
            );
        } else {
            return (
                <ContentWrapper>
                    <Spinner/>
                </ContentWrapper>
            );
        }
    }
}

Invoice.propTypes = {
    invoice: PropTypes.object,
    invoiceEntries: PropTypes.object,
    project: PropTypes.object,
    dispatch: PropTypes.func.isRequired,
    match: PropTypes.shape({
        params: PropTypes.shape({
            id: PropTypes.node,
            projectId: PropTypes.string,
            invoiceId: PropTypes.string
        }).isRequired
    }).isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.shape({
        push: PropTypes.func.isRequired
    }).isRequired,
    t: PropTypes.func.isRequired
};

const mapStateToProps = state => {
    return {
        invoice: state.invoice,
        invoiceEntries: state.invoiceEntries,
        project: state.project
    };
};

export default connect(
    mapStateToProps
)(withTranslation()(Invoice));
