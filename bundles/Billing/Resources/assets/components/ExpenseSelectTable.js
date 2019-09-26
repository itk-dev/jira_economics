import React from 'react';
import PropTypes from 'prop-types';
import { withTranslation } from 'react-i18next';
import Moment from 'react-moment';

const ExpenseSelectTable = (props) => {
    const { t } = props;

    const getNumberOfSelectedExpenses = () => {
        return props.expenses.reduce((carry, value) => {
            return carry + (value.selected ? 1 : 0);
        }, 0);
    };

    const toggleSelectAll = () => {
        if (getNumberOfSelectedExpenses() === props.expenses.length) {
            props.expenses.map((expense) => {
                if (expense.selected) {
                    props.handleSelectOnChange(expense);
                }
            });
        } else {
            props.expenses.map((expense) => {
                if (!expense.selected) {
                    props.handleSelectOnChange(expense);
                }
            });
        }
    };

    return (
        <table className={'table'}>
            <thead>
                <tr>
                    <th>
                        <input
                            name={'selectAll'}
                            type="checkbox"
                            aria-label={ getNumberOfSelectedExpenses() === props.expenses.length ? t('invoice_entry.table.deselect_all') : t('invoice_entry.table.select_all') }
                            checked={ getNumberOfSelectedExpenses() === props.expenses.length }
                            onChange={ () => { toggleSelectAll(); } }/>
                    </th>
                    <th>{t('invoice_entry.table.expense')}</th>
                    <th>{t('invoice_entry.table.category')}</th>
                    <th>{t('invoice_entry.table.billed')}</th>
                    <th>{t('invoice_entry.table.total_price')}</th>
                    <th>{t('invoice_entry.table.updated')}</th>
                </tr>
            </thead>
            <tbody>
                {
                    /* @TODO: Links to issues and expenses in Jira */
                    props.expenses.map((expense) => (
                        <tr key={expense.id} className={expense.className}>
                            <td><input
                                disabled={expense.disabled}
                                name={'expense-toggle-' + expense.id}
                                type="checkbox"
                                checked={ expense.selected }
                                onChange={ () => { props.handleSelectOnChange(expense); } }/>
                            </td>
                            <td>
                                <div>{expense.summary} ({expense.id})</div>
                                <div><i>{expense.issueSummary} ({expense.issueId})</i></div>
                            </td>
                            <td>{expense.category}</td>
                            <td>{expense.billed}</td>
                            <td>{expense.amount}</td>
                            <td>
                                <Moment format="DD-MM-YYYY">{expense.date}</Moment>
                            </td>
                        </tr>
                    ))
                }
            </tbody>
        </table>
    );
};

ExpenseSelectTable.propTypes = {
    t: PropTypes.func.isRequired,
    expenses: PropTypes.array.isRequired,
    handleSelectOnChange: PropTypes.func.isRequired
};

export default withTranslation()(ExpenseSelectTable);
