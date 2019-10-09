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
import OverlayTrigger from 'react-bootstrap/OverlayTrigger';
import Tooltip from 'react-bootstrap/Tooltip';
import ConfirmModal from '../components/ConfirmModal';
import { withTranslation, Trans } from 'react-i18next';
import Select from 'react-select';

class Invoice extends Component {
    constructor (props) {
        super(props);

        this.handleInvoiceEntryDelete = this.handleInvoiceEntryDelete.bind(this);

        this.state = {
            showDeleteModal: false,
            showRecordModal: false,
            showDeleteEntryModal: false,

            formDescription: null,
            formPaidByAccount: null,
            formAccount: null,

            invoice: null,
            invoiceEntries: {},
            entryIdToDelete: null,
            toAccounts: {},
            accounts: null
        };
    };

    componentDidMount () {
        const { dispatch } = this.props;

        dispatch(rest.actions.getProjectAccounts({ id: `${this.props.match.params.projectId}` }))
            .then((response) => {
                this.setState({ accounts: response });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getProject({ id: `${this.props.match.params.projectId}` }))
            .then((response) => {
                this.setState({ project: response });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getInvoice({ id: `${this.props.match.params.invoiceId}` }))
            .then((response) => {
                this.setState({
                    invoice: response,
                    formDescription: response.description ? response.description : '',
                    formPaidByAccount: response.paidByAccount ? response.paidByAccount : '',
                    formAccount: response.accountId ? response.accountId : ''
                });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getInvoiceEntries({ id: `${this.props.match.params.invoiceId}` }))
            .then((response) => {
                this.setState({ invoiceEntries: response });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getToAccounts())
            .then((response) => {
                this.setState({ toAccounts: response });
            })
            .catch((reason) => console.log('isCanceled', reason));
    };

    createEntry = (entryType) => {
        const { dispatch } = this.props;

        let invoiceId = this.props.match.params.invoiceId;

        let invoiceEntryData = {
            invoiceId,
            entryType
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

    handleAddFromWorklog = (event) => {
        event.preventDefault();
        this.createEntry('worklog');
    };

    handleAddManually = (event) => {
        event.preventDefault();
        this.createEntry('manual');
    };

    handleAddFromExpense = (event) => {
        event.preventDefault();
        this.createEntry('expense');
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
        dispatch(rest.actions.recordInvoice({ id: `${this.props.match.params.invoiceId}` }))
            .then(response => this.setState({
                'invoice': response
            }))
            .catch(reason => console.log(reason));
    };

    handleSubmit = (event) => {
        event.preventDefault();

        if (!this.state.invoice.id) {
            return;
        }

        let data = {
            id: this.state.invoice.id,
            description: this.state.formDescription,
            paidByAccount: this.state.formPaidByAccount,
            customerAccountId: this.state.formAccount
        };

        const { dispatch } = this.props;
        dispatch(rest.actions.updateInvoice({ id: this.state.invoice.id }, {
            body: JSON.stringify(data)
        }))
            .catch((reason) => {
                console.log(reason);
            })
        ;
    };

    handleChange (event) {
        let fieldName = event.target.name;
        let fieldVal = event.target.value;
        this.setState({ ...this.state, [fieldName]: fieldVal });
    }

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

        const accountOptions = this.state.accounts ? Object.keys(this.state.accounts).map((keyName) => {
            return {
                'value': parseInt(keyName),
                'label': keyName + ': ' + this.state.accounts[keyName].name
            };
        }) : [];

        const paidByAccountOptions = this.state.toAccounts ? Object.keys(this.state.toAccounts).map((keyName) => {
            return {
                'value': keyName,
                'label': keyName + ': ' + this.state.toAccounts[keyName].name
            };
        }) : [];

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
                            {this.state.formPaidByAccount !== null &&
                                <Form onSubmit={this.handleSubmit.bind(this)}>
                                    <Form.Group>
                                        <Form.Label htmlFor={'formDescription'}>
                                            {t('invoice.form.label.description')}
                                        </Form.Label>
                                        <Form.Control
                                            name={'formDescription'}
                                            maxLength="450"
                                            as="textarea"
                                            rows={10}
                                            disabled={this.state.invoice && this.state.invoice.recorded}
                                            onChange={this.handleChange.bind(this)}
                                            value={this.state.formDescription}
                                            placeholder={t('invoice.click_to_edit_description')}>
                                        </Form.Control>
                                        <small className="form-text text-muted mb-3">
                                            {t('invoice.form.helptext.description')}
                                        </small>

                                        <Form.Label htmlFor={'formAccount'}>
                                            {t('invoice.form.label.customer_account')}
                                        </Form.Label>
                                        { this.state.accounts &&
                                            <Select
                                                value={ accountOptions.filter(item => this.state.formAccount === item.value) }
                                                name={'formAccount'}
                                                placeholder={t('invoice.form.select_account')}
                                                aria-label={t('invoice.form.label.customer_account')}
                                                isSearchable={true}
                                                onChange={
                                                    selectedOption => {
                                                        this.setState({ formAccount: selectedOption.value });
                                                    }
                                                }
                                                isDisabled={this.state.invoice && this.state.invoice.recorded}
                                                options={accountOptions}
                                            />
                                        }
                                        <small className="form-text text-muted mb-3">
                                            {t('invoice.form.helptext.customer_account')}
                                        </small>

                                        <Form.Label htmlFor={'formPaidByAccount'}>
                                            {t('invoice.form.label.paid_by_account')}
                                        </Form.Label>
                                        { this.state.toAccounts &&
                                        <Select
                                            value={ paidByAccountOptions.filter(item => this.state.formPaidByAccount === item.value) }
                                            name={'formPaidByAccount'}
                                            isSearchable={true}
                                            placeholder={t('invoice.form.select_account')}
                                            aria-label={t('invoice.form.label.paid_by_account')}
                                            onChange={
                                                selectedOption => {
                                                    this.setState({ formPaidByAccount: selectedOption.value });
                                                }
                                            }
                                            isDisabled={this.state.invoice && this.state.invoice.recorded}
                                            options={paidByAccountOptions}
                                        />
                                        }
                                        <small className="form-text text-muted mb-3">
                                            {t('invoice.form.helptext.paid_by_account')}
                                        </small>
                                    </Form.Group>
                                    {this.state.invoice && !this.state.invoice.recorded &&
                                        <input type="submit" value={t('invoice.submit_form')} className={'btn btn-primary'}/>
                                    }
                                </Form>
                            }
                        </div>
                        <div className="col-md-6">
                            {this.state.invoice && !this.state.invoice.recorded &&
                                <div className="row mb-3">
                                    <div className="col-md-12 text-right">
                                        <ButtonGroup aria-label="Invoice actions">
                                            <Button variant="primary" type="submit"
                                                id="record-invoice" onClick={() => { this.setState({ showRecordModal: true }); }}>
                                                {t('invoice.record_invoice')}
                                            </Button>
                                            <Button
                                                variant={'secondary'}
                                                href={'/jira/billing/show_export_invoice/' + this.state.invoice.id}>
                                                {t('invoice.show_export_invoice')}
                                            </Button>
                                            <Button variant="danger" type="submit"
                                                id="delete" onClick={() => { this.setState({ showDeleteModal: true }); }}>
                                                {t('invoice.delete_invoice')}
                                            </Button>
                                        </ButtonGroup>
                                    </div>
                                </div>
                            }
                            {
                                this.props.invoice.data.account &&
                                <ListGroup>
                                    <ListGroup.Item>
                                        <strong>{t('invoice.client_information')}</strong>
                                    </ListGroup.Item>
                                    <ListGroup.Item>
                                        <span className="text-muted d-inline-block w-25">
                                            {t('invoice.client_name')}
                                        </span>{this.props.invoice.data.account.name}
                                    </ListGroup.Item>
                                    <ListGroup.Item>
                                        <span className="text-muted d-inline-block w-25">
                                            {t('invoice.client_contact')}
                                        </span>{this.props.invoice.data.account.contact ? this.props.invoice.data.account.contact.name : ''}
                                    </ListGroup.Item>
                                    <ListGroup.Item>
                                        <span className="text-muted d-inline-block w-25">
                                            {t('invoice.client_default_price')}
                                        </span>{this.props.invoice.data.account.defaultPrice}
                                    </ListGroup.Item>
                                    <ListGroup.Item>
                                        <span className="text-muted d-inline-block w-25">
                                            {t('invoice.client_type')}
                                        </span>{this.props.invoice.data.account.category.name}
                                    </ListGroup.Item>
                                    <ListGroup.Item>
                                        <span
                                            className="text-muted d-inline-block w-25">
                                            {t('invoice.client_account')}
                                        </span>{this.props.invoice.data.account.customer ? this.props.invoice.data.account.customer.key : ''}
                                    </ListGroup.Item>
                                    {this.props.invoice.data.account.category.name === 'INTERN' &&
                                        <ListGroup.Item>
                                            <span
                                                className="text-muted d-inline-block w-25">
                                                {t('invoice.client_psp')}
                                            </span>{this.props.invoice.data.account.key}
                                        </ListGroup.Item>
                                    }
                                    {this.props.invoice.data.account.category.name === 'EKSTERN' &&
                                        <ListGroup.Item>
                                            <span
                                                className="text-muted d-inline-block w-25">
                                                {t('invoice.client_ean')}
                                            </span>{this.props.invoice.data.account.key}
                                        </ListGroup.Item>
                                    }
                                </ListGroup>
                            }
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                            <h2>{t('invoice.invoice_entries_list_title')}</h2>
                            {this.state.invoice && !this.state.invoice.recorded &&
                                <div className="row mb-3">
                                    <div className="col-md-12">
                                        <Button variant="outline-success"
                                            type="submit" className="mr-3"
                                            onClick={this.handleAddFromWorklog}>{t('invoice.add_from_worklog')}</Button>
                                        <Button variant="outline-success"
                                            type="submit" className="mr-3"
                                            onClick={this.handleAddFromExpense}>{t('invoice.add_from_expense')}</Button>
                                        <Button variant="outline-success"
                                            type="submit"
                                            onClick={this.handleAddManually}>{t('invoice.add_new_manual_entry')}</Button>
                                    </div>
                                </div>
                            }
                            {this.props.invoiceEntries.loading &&
                                <Spinner/>
                            }
                            {!this.props.invoiceEntries.loading &&
                                <Table responsive hover className="table-borderless bg-light">
                                    <thead>
                                        <tr>
                                            <th>{t('invoice.table.account')}</th>
                                            <th>{t('invoice.table.material_number')}</th>
                                            <th>{t('invoice.table.product')}</th>
                                            <th>{t('invoice.table.description')}</th>
                                            <th>{t('invoice.table.amount')}</th>
                                            <th>{t('invoice.table.price')}</th>
                                            <th>{t('invoice.table.total_price')}</th>
                                            <th>{t('invoice.table.type')}</th>
                                            <th> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {this.state.invoiceEntries.data && this.state.invoiceEntries.data.map((item) =>
                                            <tr key={item.id}>
                                                <td>{item.account}</td>
                                                <td>{item.materialNumber}</td>
                                                <td>{item.product}</td>
                                                <td>{item.description}</td>
                                                <td>{item.amount}</td>
                                                <td>{item.price}</td>
                                                <td>{item.amount * item.price}</td>
                                                <td>
                                                    {item.entryType === 'expense' && t('invoice.form.types.expense')}
                                                    {item.entryType === 'worklog' && t('invoice.form.types.worklog')}
                                                    {item.entryType === 'manual' && t('invoice.form.types.manual')}
                                                </td>
                                                <td className="text-right">
                                                    {this.state.invoice && !this.state.invoice.recorded &&
                                                        <ButtonGroup size="sm" className="float-right" aria-label="Invoice entry functions">
                                                            <OverlayTrigger
                                                                key="edit"
                                                                placement="top"
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
                                                                    <span
                                                                        className="sr-only">{t('common.edit')}</span>
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
                                                    }
                                                </td>
                                            </tr>
                                        )}
                                        <tr key={'sum'} className={'table-light'}>
                                            <td colSpan={6}> </td>
                                            <td>
                                                {this.state.invoiceEntries.data && this.state.invoiceEntries.data.reduce((carry, item) => {
                                                    return carry + item.amount * item.price;
                                                }, 0)}
                                            </td>
                                            <td colSpan={2}> </td>
                                        </tr>
                                    </tbody>
                                </Table>
                            }
                        </div>
                    </div>
                    <ContentFooter>
                        {t('invoice.date_created')} <strong>
                            <Moment format="DD/MM YYYY HH:mm">
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
