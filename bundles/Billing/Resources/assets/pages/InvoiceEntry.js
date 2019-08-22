import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import Spinner from '../components/Spinner';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import JiraIssues from '../components/JiraIssues';

export class InvoiceEntry extends Component {
    constructor (props) {
        super(props);

        this.state = {
            jiraIssues: {},
            invoice: {},
            invoiceEntry: {
                account: '',
                amount: 0
            },
            selectJiraIssues: false,
            toAccounts: {},

            selectedToAccount: null,
            amount: null,
            price: null,
            product: null,
            description: null
        };

        this.selectJiraIssues = this.selectJiraIssues.bind(this);
        this.onAccountChange = this.onAccountChange.bind(this);
        this.handleSelectJiraIssues = this.handleSelectJiraIssues.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.setDefaultValues = this.setDefaultValues.bind(this);
    }

    componentDidMount () {
        const { dispatch } = this.props;

        dispatch(rest.actions.getInvoiceEntry({ id: this.props.match.params.invoiceEntryId }))
            .then((response) => {
                this.setState({ invoiceEntry: response }, () => {
                    this.setDefaultValues();
                });

                // Load jira issues for project if this is a jira invoice entry.
                if (response.isJiraEntry) {
                    dispatch(rest.actions.getJiraIssues({ id: this.props.match.params.projectId }))
                        .then((response) => {
                            this.setState({ jiraIssues: response });
                        })
                        .catch((reason) => console.log('isCanceled', reason));
                }
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getInvoice({ id: this.props.match.params.invoiceId }))
            .then((response) => {
                this.setState({ invoice: response }, () => {
                    this.setDefaultValues();
                });
            })
            .catch((reason) => console.log('isCanceled', reason));

        dispatch(rest.actions.getToAccounts())
            .then((response) => {
                this.setState({ toAccounts: response });
            })
            .catch((reason) => console.log('isCanceled', reason));
    }

    setDefaultValues = () => {
        if (this.state.invoice.hasOwnProperty('account') && this.state.invoiceEntry.hasOwnProperty('product')) {
            this.setState({
                selectedToAccount: this.state.invoiceEntry.account ? this.state.invoiceEntry.account : '',
                amount: this.state.invoiceEntry.amount ? this.state.invoiceEntry.amount : 0,
                price: this.state.invoiceEntry.price ? this.state.invoiceEntry.price : this.state.invoice.account.defaultPrice,
                product: this.state.invoiceEntry.product ? this.state.invoiceEntry.product : '',
                description: this.state.invoiceEntry.description ? this.state.invoiceEntry.description : ''
            });
        }
    };

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
        let description = this.state.description;
        let price = parseFloat(this.state.price);
        let product = this.state.product;
        let amount = this.state.amount;
        let id = this.state.invoiceEntry.id;

        let jiraIssueIds = this.state.invoiceEntry.jiraIssues.reduce(function (carry, item) {
            carry.push(item.id);
            return carry;
        }, []);

        let invoiceEntryData = {
            id,
            invoiceId,
            description,
            account,
            product,
            jiraIssueIds,
            price,
            amount
        };

        dispatch(rest.actions.updateInvoiceEntry({ id: invoiceEntryData.id }, {
            body: JSON.stringify(invoiceEntryData)
        }))
            .then((response) => {
                this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
            })
            .catch((reason) => {
                // @TODO: Warn about error.
                console.log('isCanceled', reason);
            });
    };

    handleCancel = (event) => {
        event.preventDefault();
        this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
    };

    handleChange (event) {
        let fieldName = event.target.name;
        let fieldVal = event.target.value;
        this.setState({ ...this.state, [fieldName]: fieldVal });
    }

    selectJiraIssues = () => {
        this.setState({ selectJiraIssues: true });
    };

    handleSelectJiraIssues = (issues) => {
        let timeSpent = issues.reduce((carry, item) => {
            return carry + item.timeSpent;
        }, 0);

        this.setState({
            invoiceEntry: {
                ...this.state.invoiceEntry,
                jiraIssues: issues
            },
            amount: timeSpent,
            selectJiraIssues: false
        });
    };

    handleCancelSelectJiraIssues = () => {
        this.setState({ selectJiraIssues: false });
    };

    render () {
        if (this.state.selectJiraIssues) {
            return (
                <ContentWrapper>
                    <JiraIssues
                        jiraIssues={this.state.jiraIssues}
                        selectedIssues={this.state.invoiceEntry.jiraIssues}
                        handleSelectJiraIssues={this.handleSelectJiraIssues}
                        handleCancelSelectJiraIssues={this.handleCancelSelectJiraIssues}
                    />
                </ContentWrapper>
            );
        } else if (
            this.state.toAccounts !== {} &&
            this.state.invoice !== {} &&
            this.state.invoiceEntry &&
            this.state.invoiceEntry !== {} &&
            this.state.invoice.account &&
            this.state.amount !== null &&
            this.state.price !== null &&
            this.state.description !== null &&
            this.state.selectedToAccount !== null &&
            this.state.product !== null
        ) {
            return (
                <ContentWrapper>
                    <div><PageTitle>Udfyld fakturalinje</PageTitle></div>
                    {this.state.invoiceEntry.isJiraEntry &&
                    <div>
                        <Button onClick={this.selectJiraIssues}>Vælg Jira issues</Button>
                    </div>
                    }
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
                                    <Form.Control as="select" name={'selectedToAccount'} onChange={this.handleChange} defaultValue={this.state.account ? this.state.account : this.state.invoiceEntry.account}>
                                        <option value=""> </option>
                                        {this.state.hasOwnProperty('toAccounts') && Object.keys(this.state.toAccounts)
                                            .map((keyName) => (
                                                this.state.toAccounts.hasOwnProperty(keyName) &&
                                                <option
                                                    key={this.state.toAccounts[keyName]}
                                                    value={this.state.toAccounts[keyName]}>
                                                    {keyName}: {this.state.toAccounts[keyName]}
                                                </option>
                                            ))}
                                    </Form.Control>
                                </div>
                                <label htmlFor="vare">
                                    Vare
                                </label>
                                <input
                                    type="text"
                                    name={'product'}
                                    className="form-control"
                                    id="invoice-entry-product"
                                    aria-describedby="enterVarenr"
                                    onChange={this.handleChange}
                                    defaultValue={ this.state.product }
                                    placeholder="Varenavn">
                                </input>
                                <label htmlFor="beskrivelse">
                                    Beskrivelse
                                </label>
                                <input
                                    type="text"
                                    name={'description'}
                                    className="form-control"
                                    id="invoice-entry-description"
                                    aria-describedby="enterBeskrivelse"
                                    onChange={this.handleChange}
                                    defaultValue={ this.state.description }
                                    placeholder="Varebeskrivelse">
                                </input>
                                <label htmlFor="antal">
                                    Timer
                                </label>
                                <input
                                    type="text"
                                    name={'amount'}
                                    className="form-control"
                                    id="invoice-entry-hours-spent"
                                    aria-describedby="enterHoursSpent"
                                    onChange={this.handleChange}
                                    defaultValue={ this.state.amount }
                                    readOnly={ this.state.invoiceEntry.isJiraEntry }>
                                </input>
                                <label htmlFor="beskrivelse">
                                    Stk. pris
                                </label>
                                <input
                                    type="text"
                                    name={'price'}
                                    className="form-control"
                                    id="invoice-entry-unit-price"
                                    aria-describedby="enterUnitPrice"
                                    onChange={this.handleChange}
                                    defaultValue={ this.state.price }>
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

InvoiceEntry.propTypes = {
    dispatch: PropTypes.func.isRequired,
    match: PropTypes.shape({
        params: PropTypes.shape({
            id: PropTypes.node,
            projectId: PropTypes.string,
            invoiceId: PropTypes.string,
            invoiceEntryId: PropTypes.string
        }).isRequired
    }).isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.shape({
        push: PropTypes.func.isRequired
    }).isRequired
};

const mapStateToProps = state => {
    return {};
};

export default connect(
    mapStateToProps
)(InvoiceEntry);
