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
import 'react-datepicker/dist/react-datepicker.css';
import '../css/react-datepicker.scss';
import { withTranslation } from 'react-i18next';
import InvoiceEntryJiraFilter from '../components/InvoiceEntryJiraFilter';
import WorklogSelectTable from '../components/WorklogSelectTable';

export class InvoiceEntry extends Component {
    constructor (props) {
        super(props);

        this.state = {
            // Entities:
            invoice: {},
            invoiceEntry: {
                account: '',
                amount: 0
            },

            // Lists:
            projectExpenses: null,
            projectWorklogs: null,
            toAccounts: {},

            // Selections:
            selectedToAccount: null,
            selectedWorklogs: {},

            // Form values:
            amount: null,
            price: null,
            product: null,
            description: null,

            // Filter values:
            filterValues: {
                billedFilter: 'not_billed',
                workerFilter: '',
                startDateFilter: '',
                endDateFilter: '',
                epicFilter: '',
                versionFilter: ''
            },

            // UI state:
            displaySelectionScreen: false,
            initialized: false
        };

        this.handleOpenSelectWorklogs = this.handleOpenSelectWorklogs.bind(this);
        this.onAccountChange = this.onAccountChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.setDefaultValues = this.setDefaultValues.bind(this);
        this.handleWorklogToggle = this.handleWorklogToggle.bind(this);
        this.isReady = this.isReady.bind(this);
    }

    componentDidMount () {
        const { dispatch } = this.props;

        dispatch(rest.actions.getInvoiceEntry({ id: this.props.match.params.invoiceEntryId }))
            .then((response) => {
                if (response.entryType === 'worklog') {
                    dispatch(rest.actions.getProjectWorklogs({ id: this.props.match.params.projectId }))
                        .then((response) => {
                            this.setState({ projectWorklogs: response });
                        })
                        .catch((reason) => console.log('isCancelled', reason));
                } else if (response.entryType === 'expense') {
                    dispatch(rest.actions.getProjectExpenses({ id: this.props.match.params.projectId }))
                        .then((response) => {
                            this.setState({ projectExpenses: response });
                        })
                        .catch((reason) => console.log('isCancelled', reason));
                }

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
                amount: this.state.invoiceEntry.amount ? this.state.invoiceEntry.amount : 0,
                description: this.state.invoiceEntry.description ? this.state.invoiceEntry.description : '',
                price: this.state.invoiceEntry.price ? this.state.invoiceEntry.price : this.state.invoice.account.defaultPrice,
                product: this.state.invoiceEntry.product ? this.state.invoiceEntry.product : '',
                selectedToAccount: this.state.invoiceEntry.account ? this.state.invoiceEntry.account : '',
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
            displaySelectionScreen: true
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
            displaySelectionScreen: false
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
        if (this.state.filterValues.billedFilter !== '') {
            if (this.state.filterValues.billedFilter === 'not_billed' &&
                worklog.attributes.hasOwnProperty('_Billed_') &&
                worklog.attributes['_Billed_'].value === 'true') {
                return false;
            }

            if (this.state.filterValues.billedFilter === 'billed' && (
                !worklog.attributes.hasOwnProperty('_Billed_') ||
                worklog.attributes['_Billed_'].value !== 'true')) {
                return false;
            }
        }

        if (this.state.filterValues.workerFilter !== '') {
            if (worklog.worker !== this.state.filterValues.workerFilter) {
                return false;
            }
        }

        let worklogUpdatedTimestamp = (new Date(worklog.dateUpdated)).getTime();

        if (this.state.filterValues.startDateFilter !== null && this.state.filterValues.startDateFilter !== '') {
            let startFilterTimestamp = this.state.filterValues.startDateFilter.getTime();

            if (startFilterTimestamp > worklogUpdatedTimestamp) {
                return false;
            }
        }

        if (this.state.filterValues.endDateFilter !== null && this.state.filterValues.endDateFilter !== '') {
            let endDate = this.state.filterValues.endDateFilter;
            endDate.setHours(23, 59, 59);
            let endFilterTimestamp = endDate.getTime();

            if (endFilterTimestamp < worklogUpdatedTimestamp) {
                return false;
            }
        }

        if (this.state.filterValues.versionFilter !== null && this.state.filterValues.versionFilter !== '') {
            if (!worklog.issue.versions.hasOwnProperty(this.state.filterValues.versionFilter)) {
                return false;
            }
        }

        if (this.state.filterValues.epicFilter !== null && this.state.filterValues.epicFilter !== '') {
            if (worklog.issue.epicKey !== this.state.filterValues.epicFilter) {
                return false;
            }
        }

        return true;
    };

    handleFilterChange (event) {
        const fieldName = event.target.name;
        const fieldVal = event.target.value;

        this.setState(prevState => ({
            ...prevState,
            filterValues: {
                ...prevState.filterValues,
                [fieldName]: fieldVal
            }
        }));
    }

    isReady = () => {
        return this.state.initialized;
    };

    render () {
        const { t } = this.props;

        // Show spinner if data is not ready.
        if (!this.isReady()) {
            return (
                <ContentWrapper>
                    <Spinner/>
                </ContentWrapper>
            );
        }

        // Test for whether invoice entry form or worklog/expenses selection
        // should be displayed.
        if (this.state.displaySelectionScreen) {
            if (this.state.invoiceEntry.entryType === 'worklog') {
                return (
                    <ContentWrapper>
                        <InvoiceEntryJiraFilter
                            handleChange={this.handleFilterChange.bind(this)}
                            handleStartDateChange={(newDate) => {
                                this.setState((prevState) => ({
                                    filterValues: {
                                        ...prevState.filterValues,
                                        startDateFilter: newDate
                                    }
                                }));
                            }}
                            handleEndDateChange={(newDate) => {
                                this.setState((prevState) => ({
                                    filterValues: {
                                        ...prevState.filterValues,
                                        endDateFilter: newDate
                                    }
                                }));
                            }}
                            filterValues={this.state.filterValues}
                            epics={this.state.projectWorklogs.data
                                .reduce((carry, worklog) => {
                                    if (worklog.issue.epicKey && !carry.hasOwnProperty(worklog.issue.epicKey)) {
                                        carry[worklog.issue.epicKey] = worklog.issue.epicName;
                                    }

                                    return carry;
                                }, {})}
                            versions={this.state.projectWorklogs.data
                                .reduce((carry, worklog) => {
                                    for (let versionKey in worklog.issue.versions) {
                                        if (worklog.issue.versions.hasOwnProperty(versionKey) &&
                                            !carry.hasOwnProperty(versionKey)) {
                                            carry[versionKey] = worklog.issue.versions[versionKey];
                                        }
                                    }
                                    return carry;
                                }, {})}
                            workers={this.state.projectWorklogs.data
                                .reduce((carry, worklog) => {
                                    if (carry.indexOf(worklog.worker) === -1) {
                                        carry.push(worklog.worker);
                                    }
                                    return carry;
                                }, [])}
                        />

                        <WorklogSelectTable
                            worklogs={this.state.projectWorklogs.data.filter(this.filterWorklogs.bind(this))
                                .map((worklog) => {
                                    return {
                                        tempoWorklogId: worklog.tempoWorklogId,
                                        className: (worklog.hasOwnProperty('addedToInvoiceEntryId') &&
                                            worklog.addedToInvoiceEntryId !== this.state.invoiceEntry.id) ? 'bg-secondary' : '',
                                        disabled: worklog.hasOwnProperty('addedToInvoiceEntryId') && worklog.addedToInvoiceEntryId !== this.state.invoiceEntry.id,
                                        checked: this.state.selectedWorklogs.hasOwnProperty(worklog.tempoWorklogId) ? this.state.selectedWorklogs[worklog.tempoWorklogId] : false,
                                        issueSummary: worklog.issue.summary,
                                        comment: worklog.comment,
                                        issueId: worklog.issue.id,
                                        epicName: worklog.issue.epicName,
                                        versions: worklog.issue.versions,
                                        worker: worklog.worker,
                                        billed: worklog.attributes.hasOwnProperty('_Billed_') && worklog.attributes['_Billed_'].value === 'true' ? t('invoice_entry.billed_text') : '',
                                        timeSpent: worklog.timeSpent,
                                        dateUpdated: worklog.dateUpdated
                                    };
                                })}
                            handleSelectOnChange={this.handleWorklogToggle.bind(this)}
                        />

                        <ButtonGroup>
                            <Button
                                onClick={this.handleSelectWorklogs.bind(this)}>{t('invoice_entry.save_choices')}</Button>
                        </ButtonGroup>
                    </ContentWrapper>
                );
            } else if (this.state.invoiceEntry.entryType === 'expense') {

            }
        } else {
            return (
                <ContentWrapper>
                    <div><PageTitle>{t('invoice_entry.title')}</PageTitle></div>
                    {this.state.invoiceEntry.entryType === 'worklog' &&
                        <div>
                            <Button onClick={this.handleOpenSelectWorklogs}>{t('invoice_entry.choose_worklogs')}</Button>
                        </div>
                    }
                    {/* @TODO: Move to component */}
                    <Form onSubmit={this.handleSubmit}>
                        <div>
                            <label htmlFor="selectedToAccount">
                                {t('invoice_entry.form.toAccount')}
                            </label>
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
                            <label htmlFor="product">
                                {t('invoice_entry.form.product')}
                            </label>
                            <input
                                type="text"
                                name={'product'}
                                className="form-control"
                                id="invoice-entry-product"
                                aria-describedby="enterVarenr"
                                onChange={this.handleChange}
                                defaultValue={ this.state.product }
                                placeholder={t('invoice_entry.form.product_placeholder')}>
                            </input>
                            <label htmlFor="description">
                                {t('invoice_entry.form.description')}
                            </label>
                            <input
                                type="text"
                                name={'description'}
                                className="form-control"
                                id="invoice-entry-description"
                                aria-describedby="enterBeskrivelse"
                                onChange={this.handleChange}
                                defaultValue={ this.state.description }
                                placeholder={t('invoice_entry.form.description_placeholder')}>
                            </input>
                            <label htmlFor="amount">
                                {t('invoice_entry.form.amount')}
                            </label>
                            <input
                                type="text"
                                name={'amount'}
                                className="form-control"
                                id="invoice-entry-hours-spent"
                                aria-describedby="enterHoursSpent"
                                onChange={this.handleChange}
                                defaultValue={ this.state.amount }
                                readOnly={ ['worklog', 'expense'].indexOf(this.state.invoiceEntry.entryType) !== -1 }>
                            </input>
                            <label htmlFor="price">
                                {t('invoice_entry.form.price')}
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
                            id="create-invoice-entry">{t('invoice_entry.form.submit')}
                        </button>
                    </Form>
                    <form onSubmit={this.handleCancel}>
                        <button
                            type="submit"
                            className="btn btn-secondary"
                            id="cancel">{t('invoice_entry.form.cancel')}
                        </button>
                    </form>
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
    }).isRequired,
    t: PropTypes.func.isRequired
};

const mapStateToProps = state => {
    return {};
};

export default connect(
    mapStateToProps
)(withTranslation()(InvoiceEntry));
