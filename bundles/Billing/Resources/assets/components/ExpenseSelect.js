import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { withTranslation } from 'react-i18next';
import ContentWrapper from './ContentWrapper';
import InvoiceEntryJiraFilter from './InvoiceEntryJiraFilter';
import ExpenseSelectTable from './ExpenseSelectTable';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Button from 'react-bootstrap/Button';

const ExpenseSelect = (props) => {
    const [filterValues, setFilterValues] = useState({
        billedFilter: '',
        startDateFilter: '',
        endDateFilter: '',
        epicFilter: '',
        versionFilter: '',
        categoryFilter: '',
        accountKeyFilter: ''
    });

    const { t } = props;

    const epics = props.expenses
        .reduce((carry, worklog) => {
            if (worklog.issue.epicKey && !carry.hasOwnProperty(worklog.issue.epicKey)) {
                carry[worklog.issue.epicKey] = worklog.issue.epicName;
            }
            return carry;
        }, {});

    const versions = props.expenses
        .reduce((carry, worklog) => {
            for (let versionKey in worklog.issue.versions) {
                if (worklog.issue.versions.hasOwnProperty(versionKey) &&
                    !carry.hasOwnProperty(versionKey)) {
                    carry[versionKey] = worklog.issue.versions[versionKey];
                }
            }
            return carry;
        }, {});

    const categories = props.expenses
        .reduce((carry, expense) => {
            if (expense.expenseCategory && !carry.hasOwnProperty(expense.expenseCategory.id)) {
                carry[expense.expenseCategory.id] = expense.expenseCategory.name;
            }
            return carry;
        }, {});

    const accountKeys = props.expenses
        .reduce((carry, expense) => {
            if (expense.issue.accountKey && !carry.hasOwnProperty(expense.issue.accountKey)) {
                carry[expense.issue.accountKey] = expense.issue.accountName;
            }
            return carry;
        }, {});

    const handleFilterChange = (field, value) => {
        setFilterValues({
            ...filterValues,
            [field]: value
        });
    };

    const filterExpenses = (item) => {
        if (filterValues.billedFilter !== '') {
            if (filterValues.billedFilter === 'not_billed' && item.billed) {
                return false;
            }

            if (filterValues.billedFilter === 'billed' && !item.billed) {
                return false;
            }
        }

        let worklogUpdatedTimestamp = (new Date(item.date)).getTime();

        if (filterValues.startDateFilter !== null && filterValues.startDateFilter !== '') {
            let startFilterTimestamp = filterValues.startDateFilter.getTime();

            if (startFilterTimestamp > worklogUpdatedTimestamp) {
                return false;
            }
        }

        if (filterValues.endDateFilter !== null && filterValues.endDateFilter !== '') {
            // Add one day since the selected end date from the datepicker is at 00:00.
            let endDate = new Date(filterValues.endDateFilter);
            endDate.setDate(endDate.getDate() + 1);

            let endFilterTimestamp = endDate.getTime();

            if (endFilterTimestamp < worklogUpdatedTimestamp) {
                return false;
            }
        }

        if (filterValues.versionFilter !== null && filterValues.versionFilter !== '') {
            if (!item.issue.versions.hasOwnProperty(filterValues.versionFilter)) {
                return false;
            }
        }

        if (filterValues.epicFilter !== null && filterValues.epicFilter !== '') {
            if (item.issue.epicKey !== filterValues.epicFilter) {
                return false;
            }
        }

        if (filterValues.categoryFilter !== null && filterValues.categoryFilter !== '') {
            if (item.expenseCategory.id !== parseInt(filterValues.categoryFilter)) {
                return false;
            }
        }

        if (filterValues.accountKeyFilter !== '') {
            if (item.issue.accountKey !== filterValues.accountKeyFilter) {
                return false;
            }
        }

        return true;
    };

    return (
        <ContentWrapper>
            <InvoiceEntryJiraFilter
                handleChange={handleFilterChange}
                handleStartDateChange={(newDate) => {
                    setFilterValues({
                        ...filterValues,
                        startDateFilter: newDate
                    });
                }}
                handleEndDateChange={(newDate) => {
                    setFilterValues({
                        ...filterValues,
                        endDateFilter: newDate
                    });
                }}
                filterValues={filterValues}
                epics={epics}
                categories={categories}
                versions={versions}
                workers={[]}
                accountKeys={accountKeys}
            />

            <ExpenseSelectTable
                expenses={props.expenses.filter(filterExpenses)
                    .map((expense) => {
                        return {
                            id: expense.id,
                            className: (expense.hasOwnProperty('addedToInvoiceEntryId') &&
                                expense.addedToInvoiceEntryId !== props.invoiceEntryId) ? 'bg-secondary' : '',
                            disabled: expense.hasOwnProperty('addedToInvoiceEntryId') && expense.addedToInvoiceEntryId !== props.invoiceEntryId,
                            selected: props.selectedExpenses.hasOwnProperty(expense.id) ? props.selectedExpenses[expense.id] : false,
                            summary: expense.description,
                            issueId: expense.issue.id,
                            issueSummary: expense.issue.fields.summary,
                            epicName: expense.issue.epicName,
                            category: expense.expenseCategory.name,
                            versions: expense.issue.versions,
                            accountKey: expense.issue.accountKey,
                            billed: expense.billed ? t('invoice_entry.billed_text') : '',
                            amount: expense.amount,
                            date: expense.date,
                            addedToOtherInvoice: expense.hasOwnProperty('addedToInvoiceEntryId') && expense.addedToInvoiceEntryId !== props.invoiceEntryId
                        };
                    })}
                handleSelectOnChange={props.handleSelectOnChange}
            />

            <ButtonGroup>
                <Button onClick={props.handleAccept}>
                    {t('invoice_entry.save_choices')}
                </Button>
            </ButtonGroup>
        </ContentWrapper>
    );
};

ExpenseSelect.propTypes = {
    t: PropTypes.func.isRequired,
    expenses: PropTypes.array.isRequired,
    invoiceEntryId: PropTypes.number.isRequired,
    selectedExpenses: PropTypes.object.isRequired,
    handleAccept: PropTypes.func.isRequired,
    handleSelectOnChange: PropTypes.func.isRequired
};

export default withTranslation()(ExpenseSelect);
