import React from 'react';
import PropTypes from 'prop-types';
import { withTranslation } from 'react-i18next';
import Moment from 'react-moment';

const WorklogSelectTable = (props) => {
    const { t } = props;

    return (
        <table className={'table'}>
            <thead>
                <tr>
                    <th> </th>
                    <th>{t('invoice_entry.table.worklog')}</th>
                    <th>{t('invoice_entry.table.billed')}</th>
                    <th>{t('invoice_entry.table.epic')}</th>
                    <th>{t('invoice_entry.table.version')}</th>
                    <th>{t('invoice_entry.table.user')}</th>
                    <th>{t('invoice_entry.table.hours_spent')}</th>
                    <th>{t('invoice_entry.table.updated')}</th>
                </tr>
            </thead>
            <tbody>
                {
                    /* @TODO: Links to issues and worklogs in Jira */
                    props.worklogs.map((worklog) => (
                        <tr key={worklog.tempoWorklogId} className={worklog.className}>
                            <td><input
                                disabled={worklog.disabled}
                                name={'worklog-toggle-' + worklog.tempoWorklogId}
                                type="checkbox"
                                checked={ worklog.selected }
                                onChange={ () => { props.handleSelectOnChange(worklog); } }/></td>
                            <td>
                                <div>{worklog.comment} ({worklog.tempoWorklogId})</div>
                                <div><i>{worklog.issueSummary} ({worklog.issueId})</i></div>
                            </td>
                            <td>{worklog.billed}</td>
                            <td>{worklog.epicName}</td>
                            <td>{Object.keys(worklog.versions).map((versionId) => (
                                <span key={versionId} className={'p-1'}>{worklog.versions[versionId]}</span>
                            ))}</td>
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
    );
}

WorklogSelectTable.propTypes = {
    t: PropTypes.func.isRequired,
    worklogs: PropTypes.array.isRequired,
    handleSelectOnChange: PropTypes.func.isRequired
};

export default withTranslation()(WorklogSelectTable);
