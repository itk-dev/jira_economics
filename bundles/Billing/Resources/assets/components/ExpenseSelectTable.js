import React from 'react';
import PropTypes from 'prop-types';
import { withTranslation } from 'react-i18next';
import Moment from 'react-moment';

const ExpenseSelectTable = (props) => {
    const { t } = props;

    return (
        <table className={'table'}>
            <thead>
                <tr>
                    <th> </th>
                    <th>{t('invoice_entry.table.expense')}</th>
                    <th>{t('invoice_entry.table.category')}</th>
                    <th>{t('invoice_entry.table.billed')}</th>
                    <th>{t('invoice_entry.table.user')}</th>
                    <th>{t('invoice_entry.table.hours_spent')}</th>
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
                                onChange={ () => { this.handleSelectOnChange(expense); } }/>
                            </td>
                            <td>
                                <div>{expense.comment} ({expense.id})</div>
                                <div><i>{expense.issueSummary} ({expense.issueId})</i></div>
                            </td>
                            <td>{expense.category}</td>
                            <td>{expense.billed}</td>
                            <td>{expense.worker}</td>
                            <td>{expense.timeSpent}</td>
                            <td>
                                <Moment format="DD-MM-YYYY">{expense.dateUpdated}</Moment>
                            </td>
                        </tr>
                    ))
                }
            </tbody>
        </table>
    );
}

ExpenseSelectTable.js.propTypes = {
    t: PropTypes.func.isRequired,
    expenses: PropTypes.array.isRequired,
    handleSelectOnChange: PropTypes.func.isRequired
};

export default withTranslation()(ExpenseSelectTable);
