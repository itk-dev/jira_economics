import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from './ContentWrapper';
import PageTitle from './PageTitle';
import { setSelectedIssues } from '../redux/actions';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import Spinner from './Spinner';
import Form from 'react-bootstrap/Form';

export class InvoiceEntrySubmitter extends Component {
    constructor (props) {
        super(props);

        this.state = {
            selectedIssues: {},
            invoice: {},
            invoiceEntry: {},
            toAccounts: {},
            selectedToAccount: ''
        };

        this.handleSelectJiraIssues = this.handleSelectJiraIssues.bind(this);
        this.onAccountChange = this.onAccountChange.bind(this);
    }

    componentDidMount () {
        const { dispatch } = this.props;

        console.log(this.props);

        dispatch(rest.actions.getInvoice({ id: this.props.match.params.invoiceId }))
            .then((response) => {
                this.setState({ invoice: response });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getToAccounts())
            .then((response) => {
                this.setState({ toAccounts: response });
            })
            .catch((reason) => console.log('isCanceled', reason));

        if (this.props.location.state && this.props.location.state.existingInvoiceEntryId) {
            dispatch(rest.actions.getInvoiceEntry({ id: this.props.location.state.existingInvoiceEntryId }))
                .then((response) => {
                    this.setState({ invoiceEntry: response });
                })
                .catch((reason) => console.log('isCanceled', reason));
        }
    }

    handleSelectJiraIssues = (event) => {
        event.preventDefault();
        const { dispatch } = this.props;
        dispatch(setSelectedIssues(this.props.selectedIssues.selectedIssues));
        this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}/invoice_entry/jira_issues`);
    };

    getTimeSpent () {
        if (this.props.selectedIssues === undefined || this.props.selectedIssues.selectedIssues === undefined) {
            return 0;
        }
        let timeSum = 0;
        this.props.selectedIssues.selectedIssues.forEach(selectedIssue => {
            if (parseFloat(selectedIssue.timeSpent)) {
                timeSum += selectedIssue.timeSpent;
            }
        });
        return timeSum;
    }

    onAccountChange (event) {
        this.setState({
            account: event.target.value
        });
    }

    handleSubmit = (event) => {
        event.preventDefault();
        const { dispatch } = this.props;
        const invoiceId = this.state.invoice.id;

        let account = this.state.selectedToAccount;
        let description = event.target.description.value;
        let price = parseFloat(event.target.unitPrice.value);
        let product = event.target.productName.value;
        let amount = event.target.hoursSpent.value;

        let jiraIssueIds = [];
        if (this.props.selectedIssues && this.props.selectedIssues.selectedIssues && this.props.selectedIssues.selectedIssues.length > 0) {
            this.props.selectedIssues.selectedIssues.forEach(selectedIssue => {
                jiraIssueIds.push(selectedIssue.id);
            });
        }

        let invoiceEntryData = {
            invoiceId,
            description,
            account,
            product,
            jiraIssueIds,
            price,
            amount
        };

        invoiceEntryData.id = this.props.location.state.existingInvoiceEntryId;
        if (invoiceEntryData.id) {
            dispatch(rest.actions.updateInvoiceEntry({ id: invoiceEntryData.id }, {
                body: JSON.stringify(invoiceEntryData)
            }))
                .then((response) => {
                    dispatch(setSelectedIssues([]));
                    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
                })
                .catch((reason) => {
                    // @TODO: Warn about error.
                    console.log('isCanceled', reason.isCanceled);
                });
        } else {
            dispatch(rest.actions.createInvoiceEntry({}, {
                body: JSON.stringify(invoiceEntryData)
            }))
                .then((response) => {
                    dispatch(setSelectedIssues([]));
                    this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
                })
                .catch((reason) => {
                    // @TODO: Warn about error.
                    console.log('isCanceled', reason.isCanceled);
                });
        }
    };

    handleCancel = (event) => {
        event.preventDefault();
        const { dispatch } = this.props;
        dispatch(setSelectedIssues([]));
        this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
    };

    handleChange (event) {
        let fieldName = event.target.name;
        let fieldVal = event.target.value;
        this.setState({ ...this.state, [fieldName]: fieldVal });
    }

    render () {
        if (this.props.toAccounts !== {} && this.props.invoice !== {}) {
            let pageTitle;
            // Editing an existing InvoiceEntry.
            if (this.props.location.state.existingInvoiceEntryId) {
                pageTitle =
                    <PageTitle>Rediger eksisterende fakturalinje</PageTitle>;
            } else {
                // Creating a new InvoiceEntry with JiraIssues.
                pageTitle =
                    <PageTitle>Opret fakturalinje med issues fra Jira</PageTitle>;
            }

            console.log('PROPS', this.props);

            return (
                <ContentWrapper>
                    <div>{pageTitle}</div>
                    <div>{Object.values(this.props.selectedIssues.selectedIssues).length + ' issue(s) valgt'}</div>
                    <div>{'Total timer valgt: ' + this.getTimeSpent()}</div>
                    <div>
                        <form id="submitForm" onSubmit={this.handleSelectJiraIssues}>
                            <button type="submit" className="btn btn-primary" id="submit">Rediger valg
                            </button>
                        </form>
                    </div>
                    {this.props.invoiceEntry && this.props.invoice.data !== {} && this.props.invoice.data.account && this.props.invoiceEntry.data.account &&
                    <div>
                        <Form onSubmit={this.handleSubmit}>
                            <label htmlFor="kontonr">
                                Kontonr.
                            </label>
                            <div>
                            </div>
                            <div>
                                <label htmlFor="accountInput">
                                    To account
                                </label>
                                <div>
                                    <Form.Control as="select" name={'selectedToAccount'} onChange={this.handleChange.bind(this)} defaultValue={this.props.invoiceEntry.data.account}>
                                        <option value=''> </option>
                                        {this.props.toAccounts !== {} && Object.keys(this.props.toAccounts.data)
                                            .map((keyName) => (
                                                this.props.toAccounts.data.hasOwnProperty(keyName) &&
                                                <option
                                                    key={this.props.toAccounts.data[keyName]}
                                                    value={this.props.toAccounts.data[keyName]}>
                                                    {keyName}: {this.props.toAccounts.data[keyName]}
                                                </option>
                                            ))}
                                    </Form.Control>
                                </div>
                                <label htmlFor="vare">
                                    Vare
                                </label>
                                <input
                                    type="text"
                                    name="productName"
                                    className="form-control"
                                    id="invoice-entry-product"
                                    aria-describedby="enterVarenr"
                                    defaultValue={ this.props.invoiceEntry.data.product }
                                    placeholder="Varenavn">
                                </input>
                                <label htmlFor="beskrivelse">
                                    Beskrivelse
                                </label>
                                <input
                                    type="text"
                                    name="description"
                                    className="form-control"
                                    id="invoice-entry-description"
                                    aria-describedby="enterBeskrivelse"
                                    defaultValue={ this.props.invoiceEntry.data.description }
                                    placeholder="Varebeskrivelse">
                                </input>
                                <label htmlFor="antal">
                                    Timer
                                </label>
                                <input
                                    type="text"
                                    name="hoursSpent"
                                    className="form-control"
                                    id="invoice-entry-hours-spent"
                                    aria-describedby="enterHoursSpent"
                                    value={this.getTimeSpent()}
                                    readOnly>
                                </input>
                                <label htmlFor="beskrivelse">
                                    Stk. pris
                                </label>
                                <input
                                    type="text"
                                    name="unitPrice"
                                    className="form-control"
                                    id="invoice-entry-unit-price"
                                    aria-describedby="enterUnitPrice"
                                    defaultValue={ this.props.invoiceEntry.data.price ? this.props.invoiceEntry.data.price : this.props.invoice.data.account.defaultPrice }>
                                </input>
                            </div>
                            <button
                                type="submit"
                                className="btn btn-primary"
                                id="create-invoice-entry">Overfør til faktura
                            </button>
                        </Form>
                        <form onSubmit={this.handleCancel}>
                            <button
                                type="submit"
                                className="btn btn-secondary"
                                id="cancel">Annuller
                            </button>
                        </form>
                    </div>
                    }
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

InvoiceEntrySubmitter.propTypes = {
    toAccounts: PropTypes.object,
    invoiceEntrySubmitter: PropTypes.object,
    invoice: PropTypes.object,
    dispatch: PropTypes.func.isRequired,
    selectedIssues: PropTypes.array,
    invoiceEntries: PropTypes.object,
    invoiceEntry: PropTypes.object,
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
    ready: PropTypes.bool
};

const mapStateToProps = state => {
    return {
        invoiceEntrySubmitter: state.invoiceEntrySubmitter,
        selectedIssues: state.selectedIssues,
        invoiceEntries: state.invoiceEntries,
        invoiceEntry: state.invoiceEntry,
        invoice: state.invoice,
        toAccounts: state.toAccounts,
        ready: state.ready
    };
};

export default connect(
    mapStateToProps
)(InvoiceEntrySubmitter);
