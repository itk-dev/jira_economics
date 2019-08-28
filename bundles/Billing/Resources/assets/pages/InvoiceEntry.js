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
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import '../css/react-datepicker.scss';

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
            selectedWorklogs: {},
            displaySelectWorklogs: false,

            amount: null,
            price: null,
            product: null,
            description: null,

            billedFilter: 'not_billed',
            workerFilter: '',
            startDateFilter: '',
            endDateFilter: ''
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

        dispatch(rest.actions.getProjectWorklogs({ id: this.props.match.params.projectId }))
            .then((response) => {
                this.setState({ projectWorklogs: response });
            })
            .catch((reason) => console.log('isCancelled', reason));

        dispatch(rest.actions.getInvoiceEntry({ id: this.props.match.params.invoiceEntryId }))
            .then((response) => {
                this.setState({ invoiceEntry: response }, () => {
                    this.setDefaultValues();
                });
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
                description: this.state.invoiceEntry.description ? this.state.invoiceEntry.description : '',
                selectedWorklogs: this.state.invoiceEntry.worklogIds
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

        for (let worklogKey in this.state.projectWorklogs.data) {
            let worklog = this.state.projectWorklogs.data[worklogKey];

            if (this.state.selectedWorklogs.hasOwnProperty(worklog.tempoWorklogId) &&
                this.state.selectedWorklogs[worklog.tempoWorklogId]) {
                timeSpent = timeSpent + worklog.timeSpentSeconds;
            }
        }

        this.setState({
            amount: timeSpent / 60 / 60,
            displaySelectWorklogs: false
        });
    };

    handleWorklogToggle = (worklog) => {
        let selectedWorklogs = this.state.selectedWorklogs;
        selectedWorklogs[worklog.tempoWorklogId] = !selectedWorklogs[worklog.tempoWorklogId];

        this.setState({
            selectedWorklogs: selectedWorklogs
        });
    };

    filterWorklogs = (worklog) => {
        if (this.state.billedFilter !== '') {
            if (this.state.billedFilter === 'not_billed' &&
                worklog.attributes.hasOwnProperty('_Billed_') &&
                worklog.attributes['_Billed_'].value === 'true') {
                return false;
            }

            if (this.state.billedFilter === 'billed' && (
                !worklog.attributes.hasOwnProperty('_Billed_') ||
                worklog.attributes['_Billed_'].value === 'true')) {
                return false;
            }
        }

        if (this.state.workerFilter !== '') {
            if (worklog.worker !== this.state.workerFilter) {
                return false;
            }
        }

        let worklogUpdatedTimestamp = (new Date(worklog.dateUpdated)).getTime();

        if (this.state.startDateFilter !== null && this.state.startDateFilter !== '') {
            let startFilterTimestamp = this.state.startDateFilter.getTime();

            if (startFilterTimestamp > worklogUpdatedTimestamp) {
                return false;
            }
        }

        if (this.state.endDateFilter !== null && this.state.endDateFilter !== '') {
            let endDate = this.state.endDateFilter;
            endDate.setHours(23, 59, 59);
            let endFilterTimestamp = endDate.getTime();

            if (endFilterTimestamp < worklogUpdatedTimestamp) {
                return false;
            }
        }

        return true;
    };

    render () {
        if (this.state.displaySelectWorklogs) {
            if (!this.state.projectWorklogs || !this.state.projectWorklogs.data || this.state.projectWorklogs.loading) {
                return (
                    <ContentWrapper>
                        <Spinner/>
                    </ContentWrapper>
                );
            }

            return (
                <ContentWrapper>
                    <Form.Group>
                        <label htmlFor={'startDateFilter'}>Start date</label>
                        <DatePicker name={'startDateFilter'} className={'form-control'} selected={this.state.startDateFilter} isClearable onChange={(newDate) => { this.setState({ startDateFilter: newDate }); }} />

                        <label htmlFor={'startDateFilter'}>Start date</label>
                        <DatePicker name={'endDateFilter'} className={'form-control'} selected={this.state.endDateFilter} isClearable onChange={(newDate) => { this.setState({ endDateFilter: newDate }); }} />

                        <label htmlFor={'billedFilter'}>Billed</label>
                        <select
                            name={'billedFilter'}
                            className={'form-control'}
                            value={this.state.billedFilter}
                            onChange={this.handleChange}>
                            <option value={''}>
                                All
                            </option>
                            <option value={'not_billed'}>
                                Not billed
                            </option>
                            <option value={'billed'}>
                                Billed
                            </option>
                        </select>

                        <label htmlFor={'workerFilter'}>Worker</label>
                        <select
                            name={'workerFilter'}
                            className={'form-control'}
                            value={this.state.workerFilter}
                            onChange={this.handleChange}>
                            <option value={''}>
                                All
                            </option>
                            {this.state.projectWorklogs.data
                                .reduce((carry, worklog) => {
                                    if (carry.indexOf(worklog.worker) === -1) {
                                        carry.push(worklog.worker);
                                    }
                                    return carry;
                                }, [])
                                .map((worker) => (
                                    <option key={worker} value={worker}>
                                        {worker}
                                    </option>
                                ))
                            }
                        </select>
                    </Form.Group>

                    <table className={'table'}>
                        <thead>
                            <tr>
                                <th> </th>
                                <th>Worklog</th>
                                <th>Billed</th>
                                <th>User</th>
                                <th>Time spent (hours)</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            {
                                /* @TODO: Links to issues and worklogs in Jira */
                                this.state.projectWorklogs.data.filter(this.filterWorklogs.bind(this)).map((worklog) => (
                                    <tr key={worklog.tempoWorklogId}>
                                        <td><input
                                            name={'worklog-toggle-' + worklog.tempoWorklogId}
                                            type="checkbox"
                                            checked={ this.state.selectedWorklogs.hasOwnProperty(worklog.tempoWorklogId) ? this.state.selectedWorklogs[worklog.tempoWorklogId] : false }
                                            onChange={ () => { this.handleWorklogToggle(worklog); } }/></td>
                                        <td>
                                            <div>{worklog.comment} ({worklog.tempoWorklogId})</div>
                                            <div><i>{worklog.issue.summary} ({worklog.issue.id})</i></div>
                                        </td>
                                        <td>{worklog.attributes.hasOwnProperty('_Billed_') && worklog.attributes['_Billed_'].value === 'true' ? 'Yes' : ''}</td>
                                        <td>{worklog.worker}</td>
                                        <td>{worklog.timeSpent}</td>
                                        <td>
                                            <Moment format="DD-MM-YYYY">{worklog.dateUpdated}</Moment>
                                        </td>
                                    </tr>
                                ))
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
