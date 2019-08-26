import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import Spinner from '../components/Spinner';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Moment from 'react-moment';

export class InvoiceEntry extends Component {
    constructor (props) {
        super(props);

        this.state = {
            invoice: {},
            invoiceEntry: {
                account: '',
                amount: 0
            },

            toAccounts: {},
            selectedToAccount: null,

            jiraIssues: {},
            selectedWorklogs: {},
            displaySelectWorklogs: false,

            amount: null,
            price: null,
            product: null,
            description: null
        };

        this.handleOpenSelectWorklogs = this.handleOpenSelectWorklogs.bind(this);
        this.onAccountChange = this.onAccountChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.setDefaultValues = this.setDefaultValues.bind(this);
        this.handleWorklogToggle = this.handleWorklogToggle.bind(this);
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
        let worklogIds = Object.keys(this.state.selectedWorklogs).reduce(
            (carry, worklogKey) => {
                if (this.state.selectedWorklogs[worklogKey]) {
                    carry.push(worklogKey);
                }
                return carry;
            }, []);

        let invoiceEntryData = {
            id,
            invoiceId,
            description,
            account,
            product,
            worklogIds,
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
        const fieldName = event.target.name;
        const fieldVal = event.target.value;

        this.setState(prevState => ({ ...prevState, [fieldName]: fieldVal }));
    }

    handleOpenSelectWorklogs = () => {
        this.setState({
            displaySelectWorklogs: true
        });
    };

    handleSelectWorklogs = () => {
        let timeSpent = 0;

        this.state.jiraIssues.data.map(
            (issue) => {
                for (let worklogKey in issue.worklogs) {
                    if (issue.worklogs.hasOwnProperty(worklogKey)) {
                        let worklog = issue.worklogs[worklogKey];

                        if (this.state.selectedWorklogs.hasOwnProperty(worklog.id)) {
                            timeSpent = timeSpent + worklog.timeSpentSeconds;
                        }
                    }
                }
            }
        );

        this.setState({
            amount: timeSpent / 60 / 60,
            displaySelectWorklogs: false
        });
    };

    handleWorklogToggle = (worklog) => {
        let selectedWorklogs = this.state.selectedWorklogs;
        selectedWorklogs[worklog.id] = !selectedWorklogs[worklog.id];

        this.setState({
            selectedWorklogs: selectedWorklogs
        });
    };

    render () {
        if (this.state.displaySelectWorklogs) {
            if (!this.state.jiraIssues.data || this.state.jiraIssues.loading) {
                return (
                    <ContentWrapper>
                        <Spinner/>
                    </ContentWrapper>
                );
            }

            return (
                <ContentWrapper>
                    <table className={'table'}>
                        <thead>
                            <tr>
                                <th> </th>
                                <th>Issue</th>
                                <th>Worklog id</th>
                                <th>Worklog comment</th>
                                <th>Time spent (hours)</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.jiraIssues.data && this.state.jiraIssues.data.map((issue) => (
                                issue.worklogs.map((worklog) => (
                                    <tr key={worklog.id}>
                                        <td><input
                                            name={'worklog-toggle-' + worklog.id}
                                            type="checkbox"
                                            checked={ this.state.selectedWorklogs.hasOwnProperty(worklog.id) ? this.state.selectedWorklogs[worklog.id] : false }
                                            onChange={ () => { this.handleWorklogToggle(worklog); } }/></td>
                                        <td>{issue.summary}</td>
                                        <td>{worklog.id}</td>
                                        <td>{worklog.comment}</td>
                                        <td>{worklog.timeSpent}</td>
                                        <td>
                                            <Moment format="DD-MM-YYYY">{worklog.updated}</Moment>
                                        </td>
                                    </tr>
                                ))))
                            }
                        </tbody>
                    </table>
                    <ButtonGroup>
                        <Button onClick={this.handleSelectWorklogs.bind(this)}>Gem valg</Button>
                    </ButtonGroup>
                </ContentWrapper>
            );
        } else if (
            // @TODO: Cleanup existence checks.
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
                        <Button onClick={this.handleOpenSelectWorklogs}>Vælg worklogs</Button>
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
