import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { withTranslation } from 'react-i18next';
import ContentWrapper from './ContentWrapper';
import InvoiceEntryJiraFilter from './InvoiceEntryJiraFilter';
import WorklogSelectTable from './WorklogSelectTable';
import ButtonGroup from 'react-bootstrap/ButtonGroup';
import Button from 'react-bootstrap/Button';

const WorklogSelect = (props) => {
    const [filterValues, setFilterValues] = useState({
        billedFilter: '',
        workerFilter: '',
        startDateFilter: '',
        endDateFilter: '',
        epicFilter: '',
        versionFilter: '',
        accountKeyFilter: ''
    });

    const { t } = props;

    const epics = props.worklogs
        .reduce((carry, worklog) => {
            if (worklog.issue.epicKey && !carry.hasOwnProperty(worklog.issue.epicKey)) {
                carry[worklog.issue.epicKey] = worklog.issue.epicName;
            }
            return carry;
        }, {});

    const versions = props.worklogs
        .reduce((carry, worklog) => {
            for (let versionKey in worklog.issue.versions) {
                if (worklog.issue.versions.hasOwnProperty(versionKey) &&
                    !carry.hasOwnProperty(versionKey)) {
                    carry[versionKey] = worklog.issue.versions[versionKey];
                }
            }
            return carry;
        }, {});

    const workers = props.worklogs
        .reduce((carry, worklog) => {
            if (carry.indexOf(worklog.worker) === -1) {
                carry.push(worklog.worker);
            }
            return carry;
        }, []);

    const accountKeys = props.worklogs
        .reduce((carry, worklog) => {
            if (worklog.issue.accountKey && !carry.hasOwnProperty(worklog.issue.accountKey)) {
                carry[worklog.issue.accountKey] = worklog.issue.accountName;
            }
            return carry;
        }, {});

    const handleFilterChange = (event) => {
        const fieldName = event.target.name;
        const fieldVal = event.target.value;

        setFilterValues({
            ...filterValues,
            [fieldName]: fieldVal
        });
    };

    const filterWorklogs = (item) => {
        if (filterValues.billedFilter !== '') {
            if (filterValues.billedFilter === 'not_billed' && item.billed) {
                return false;
            }

            if (filterValues.billedFilter === 'billed' && !item.billed) {
                return false;
            }
        }

        if (filterValues.workerFilter !== '') {
            if (item.worker !== filterValues.workerFilter) {
                return false;
            }
        }

        let worklogUpdatedTimestamp = (new Date(item.dateUpdated)).getTime();

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
                categories={{}}
                versions={versions}
                workers={workers}
                accountKeys={accountKeys}
            />

            <WorklogSelectTable
                worklogs={props.worklogs.filter(filterWorklogs)
                    .map((worklog) => {
                        return {
                            tempoWorklogId: worklog.tempoWorklogId,
                            className: (worklog.hasOwnProperty('addedToInvoiceEntryId') &&
                                worklog.addedToInvoiceEntryId !== props.invoiceEntryId) ? 'bg-secondary' : '',
                            disabled: worklog.hasOwnProperty('addedToInvoiceEntryId') && worklog.addedToInvoiceEntryId !== props.invoiceEntryId,
                            selected: props.selectedWorklogs.hasOwnProperty(worklog.tempoWorklogId) ? props.selectedWorklogs[worklog.tempoWorklogId] : false,
                            issueSummary: worklog.issue.summary,
                            comment: worklog.comment,
                            issueId: worklog.issue.id,
                            accountKey: worklog.issue.accountKey,
                            epicName: worklog.issue.epicName,
                            versions: worklog.issue.versions,
                            worker: worklog.worker,
                            billed: worklog.billed ? t('invoice_entry.billed_text') : '',
                            timeSpent: worklog.timeSpent,
                            dateUpdated: worklog.dateUpdated
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

WorklogSelect.propTypes = {
    t: PropTypes.func.isRequired,
    worklogs: PropTypes.array.isRequired,
    invoiceEntryId: PropTypes.number.isRequired,
    selectedWorklogs: PropTypes.object.isRequired,
    handleAccept: PropTypes.func.isRequired,
    handleSelectOnChange: PropTypes.func.isRequired
};

export default withTranslation()(WorklogSelect);
