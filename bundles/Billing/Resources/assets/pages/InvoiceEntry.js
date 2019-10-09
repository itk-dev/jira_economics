import React, { Component } from 'react';
import connect from 'react-redux/es/connect/connect';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import Spinner from '../components/Spinner';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';
import 'react-datepicker/dist/react-datepicker.css';
import '../css/react-datepicker.scss';
import { withTranslation } from 'react-i18next';
import WorklogSelect from '../components/WorklogSelect';
import ExpenseSelect from '../components/ExpenseSelect';
import Select from 'react-select';
import Bus from '../modules/Bus';

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
            materialNumbers: {},

            // Selections:
            selectedToAccount: null,
            selectedWorklogs: {},
            selectedExpenses: {},

            // Form values:
            amount: null,
            price: null,
            product: null,
            description: null,
            materialNumber: null,

            // UI state:
            displaySelectionScreen: false,
            initialized: false,
            worklogsInitialized: false,
            expensesInitialized: false
        };

        this.handleOpenSelectJiraEntries = this.handleOpenSelectJiraEntries.bind(this);
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
                if (response.entryType === 'worklog') {
                    dispatch(rest.actions.getProjectWorklogs({ id: this.props.match.params.projectId }))
                        .then((response) => {
                            this.setState({
                                projectWorklogs: response
                            });
                        })
                        .catch((reason) => {
                            Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
                        });
                } else if (response.entryType === 'expense') {
                    dispatch(rest.actions.getProjectExpenses({ id: this.props.match.params.projectId }))
                        .then((response) => {
                            this.setState({
                                projectExpenses: response
                            });
                        })
                        .catch((reason) => {
                            Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
                        });
                }

                this.setState({ invoiceEntry: response }, () => {
                    this.setDefaultValues();
                });
            })
            .catch((reason) => {
                Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
            });

        dispatch(rest.actions.getInvoice({ id: this.props.match.params.invoiceId }))
            .then((response) => {
                this.setState({ invoice: response }, () => {
                    this.setDefaultValues();
                });
            })
            .catch((reason) => {
                Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
            });

        dispatch(rest.actions.getToAccounts())
            .then((response) => {
                this.setState({ toAccounts: response });
            })
            .catch((reason) => {
                Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
            });

        dispatch(rest.actions.getMaterialNumbers())
            .then((response) => {
                this.setState({ materialNumbers: response });
            })
            .catch((reason) => {
                Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
            });
    }

    setDefaultValues = () => {
        if (this.state.invoice.hasOwnProperty('account') && this.state.invoiceEntry.hasOwnProperty('product')) {
            this.setState({
                amount: this.state.invoiceEntry.amount ? this.state.invoiceEntry.amount : 0,
                description: this.state.invoiceEntry.description ? this.state.invoiceEntry.description : '',
                price: this.state.invoiceEntry.price
                    ? this.state.invoiceEntry.price
                    : (this.state.invoice.account ? this.state.invoice.account.defaultPrice : 0),
                product: this.state.invoiceEntry.product ? this.state.invoiceEntry.product : '',
                selectedToAccount: this.state.invoiceEntry.account ? this.state.invoiceEntry.account : '',
                materialNumber: this.state.invoiceEntry.materialNumber ? parseInt(this.state.invoiceEntry.materialNumber) : '',
                selectedWorklogs: this.state.invoiceEntry.hasOwnProperty('worklogIds') ? Object.keys(this.state.invoiceEntry.worklogIds).reduce((carry, worklogId) => {
                    carry[worklogId] = true;
                    return carry;
                }, {}) : {},
                selectedExpenses: this.state.invoiceEntry.hasOwnProperty('expenseIds') ? Object.keys(this.state.invoiceEntry.expenseIds).reduce((carry, expenseId) => {
                    carry[expenseId] = true;
                    return carry;
                }, {}) : {},
                initialized: true
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
        let product = this.state.product;
        let price = parseFloat(this.state.price);
        let amount = this.state.amount;
        let id = this.state.invoiceEntry.id;
        let materialNumber = this.state.materialNumber;

        let entryData = {
            id,
            invoiceId,
            description,
            account,
            product,
            price,
            amount,
            materialNumber
        };

        switch (this.state.invoiceEntry.entryType) {
        case 'worklog':
            let worklogIds = Object.keys(this.state.selectedWorklogs).reduce(
                (carry, worklogKey) => {
                    if (this.state.selectedWorklogs[worklogKey]) {
                        carry.push(worklogKey);
                    }
                    return carry;
                }, []);

            entryData['worklogIds'] = worklogIds;

            break;
        case 'expense':
            let expenseIds = Object.keys(this.state.selectedExpenses).reduce(
                (carry, expenseKey) => {
                    if (this.state.selectedExpenses[expenseKey]) {
                        carry.push(expenseKey);
                    }
                    return carry;
                }, []);

            entryData['expenseIds'] = expenseIds;

            break;
        }

        dispatch(rest.actions.updateInvoiceEntry({ id: entryData.id }, {
            body: JSON.stringify(entryData)
        }))
            .then((response) => {
                this.props.history.push(`/project/${this.props.match.params.projectId}/${this.props.match.params.invoiceId}`);
            })
            .catch((reason) => {
                Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
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

    handleOpenSelectJiraEntries = () => {
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

    handleExpensesToggle = (expense) => {
        let selectedExpenses = this.state.selectedExpenses;
        selectedExpenses[expense.id] = !selectedExpenses[expense.id];

        this.setState({
            selectedExpenses: selectedExpenses
        });
    };

    handleSelectExpenses = () => {
        let price = 0;

        for (let expenseKey in this.state.projectExpenses.data) {
            let expense = this.state.projectExpenses.data[expenseKey];

            if (this.state.selectedExpenses.hasOwnProperty(expense.id) &&
                this.state.selectedExpenses[expense.id]) {
                price = price + expense.amount;
            }
        }

        this.setState({
            price: price,
            amount: 1,
            displaySelectionScreen: false
        });
    };

    spinner = () => (
        <ContentWrapper>
            <Spinner/>
        </ContentWrapper>
    );

    render () {
        const { t } = this.props;

        // Show spinner if data is not ready.
        if (!this.state.initialized) {
            return this.spinner();
        }

        const toAccountOptions = Object.keys(this.state.toAccounts).map((keyName) => {
            return {
                'value': keyName,
                'label': keyName + ': ' + this.state.toAccounts[keyName].name
            };
        });

        const materialOptions = Object.keys(this.state.materialNumbers).map((keyName) => {
            return {
                'value': this.state.materialNumbers[keyName],
                'label': keyName + ': ' + this.state.materialNumbers[keyName]
            };
        });

        // Test for whether invoice entry form or worklog/expenses selection
        // should be displayed.
        if (this.state.displaySelectionScreen) {
            if (this.state.invoiceEntry.entryType === 'worklog' && this.state.projectWorklogs !== null) {
                return (
                    <WorklogSelect
                        handleSelectOnChange={this.handleWorklogToggle.bind(this)}
                        worklogs={this.state.projectWorklogs.data}
                        selectedWorklogs={this.state.selectedWorklogs}
                        invoiceEntryId={this.state.invoiceEntry.id}
                        handleAccept={this.handleSelectWorklogs.bind(this)}
                    />
                );
            } else if (this.state.invoiceEntry.entryType === 'expense' && this.state.projectExpenses !== null) {
                return (
                    <ExpenseSelect
                        handleSelectOnChange={this.handleExpensesToggle.bind(this)}
                        expenses={this.state.projectExpenses.data}
                        selectedExpenses={this.state.selectedExpenses}
                        invoiceEntryId={this.state.invoiceEntry.id}
                        handleAccept={this.handleSelectExpenses.bind(this)}
                    />
                );
            } else {
                return this.spinner();
            }
        } else {
            return (
                <ContentWrapper>
                    <div><PageTitle>{t('invoice_entry.title')}</PageTitle></div>
                    {this.state.invoiceEntry.entryType !== 'manual' &&
                        <div>
                            <Button onClick={this.handleOpenSelectJiraEntries}>{t('invoice_entry.select_jira_items')}</Button>
                        </div>
                    }
                    {/* @TODO: Move to component */}
                    <Form onSubmit={this.handleSubmit}>
                        <div>
                            <label htmlFor="selectedToAccount">
                                {t('invoice_entry.form.to_account')}
                            </label>
                            <Select
                                value={toAccountOptions.filter(item => this.state.selectedToAccount === item.value)}
                                name={'selectedToAccount'}
                                placeholder={t('invoice.form.select_account')}
                                isSearchable={true}
                                aria-label={t('invoice_entry.form.to_account')}
                                onChange={
                                    selectedOption => {
                                        this.setState({ selectedToAccount: selectedOption.value });
                                    }
                                }
                                options={toAccountOptions}
                            />
                            <label htmlFor="materialNumber">
                                {t('invoice_entry.form.material_number')}
                            </label>
                            <Select
                                value={ materialOptions.filter(item => this.state.materialNumber === item.value) }
                                name={'materialNumber'}
                                placeholder={t('invoice.form.select_account')}
                                isSearchable={true}
                                aria-label={t('invoice_entry.form.material_number')}
                                onChange={
                                    selectedOption => {
                                        this.setState({ materialNumber: selectedOption.value });
                                    }
                                }
                                options={materialOptions}
                            />
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
                                value={ this.state.product }
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
                                value={ this.state.description }
                                placeholder={t('invoice_entry.form.description_placeholder')}>
                            </input>
                            <label htmlFor="amount">
                                {t('invoice_entry.form.amount')}
                            </label>
                            <input
                                type="number"
                                step="0.25"
                                name={'amount'}
                                className="form-control"
                                id="invoice-entry-hours-spent"
                                aria-describedby="enterHoursSpent"
                                onChange={this.handleChange}
                                value={this.state.amount}
                                readOnly={['worklog', 'expense'].indexOf(this.state.invoiceEntry.entryType) !== -1}>
                            </input>
                            <label htmlFor="price">
                                {t('invoice_entry.form.price')}
                            </label>
                            <input
                                type="number"
                                step="0.25"
                                name={'price'}
                                className="form-control"
                                id="invoice-entry-unit-price"
                                aria-describedby="enterUnitPrice"
                                onChange={this.handleChange}
                                value={this.state.price}
                                readOnly={['expense'].indexOf(this.state.invoiceEntry.entryType) !== -1}>
                            </input>
                            <label htmlFor="totalPrice">
                                {t('invoice_entry.form.total_price')}
                            </label>
                            <input
                                type="text"
                                name={'totalPrice'}
                                className="form-control"
                                id="invoice-entry-total-price"
                                aria-describedby="enterUnitPrice"
                                disabled={true}
                                value={ this.state.price * this.state.amount }>
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
