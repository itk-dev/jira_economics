import React from 'react';
import DatePicker from 'react-datepicker';
import Form from 'react-bootstrap/Form';
import PropTypes from 'prop-types';
import { withTranslation } from 'react-i18next';

const InvoiceEntryJiraFilter = (props) => {
    const { t } = props;

    return (
        <Form.Group>
            <label htmlFor={'startDateFilter'}>{t('invoice_entry.filter.start_date')}</label>
            <DatePicker name={'startDateFilter'} className={'form-control'} selected={props.filterValues.startDateFilter} isClearable onChange={props.handleStartDateChange} />

            <label htmlFor={'endDateFilter'}>{t('invoice_entry.filter.end_date')}</label>
            <DatePicker name={'endDateFilter'} className={'form-control'} selected={props.filterValues.endDateFilter} isClearable onChange={props.handleEndDateChange} />

            <label htmlFor={'billedFilter'}>{t('invoice_entry.filter.billed')}</label>
            <select
                name={'billedFilter'}
                className={'form-control'}
                value={props.filterValues.billedFilter}
                onChange={props.handleChange}>
                <option value={''}>
                    {t('invoice_entry.filter.billed_option.all')}
                </option>
                <option value={'not_billed'}>
                    {t('invoice_entry.filter.billed_option.not_billed')}
                </option>
                <option value={'billed'}>
                    {t('invoice_entry.filter.billed_option.billed')}
                </option>
            </select>

            <label htmlFor={'workerFilter'}>{t('invoice_entry.filter.worker')}</label>
            <select
                name={'workerFilter'}
                className={'form-control'}
                value={props.filterValues.workerFilter}
                onChange={props.handleChange}>
                <option value={''}>
                    {t('invoice_entry.filter.worker_option.all')}
                </option>
                {props.workers.map((worker) => (
                    <option key={worker} value={worker}>
                        {worker}
                    </option>
                ))}
            </select>

            <label htmlFor={'epicFilter'}>{t('invoice_entry.filter.epic')}</label>
            <select
                name={'epicFilter'}
                className={'form-control'}
                value={props.filterValues.epicFilter}
                onChange={props.handleChange}>
                <option value={''}>
                    {t('invoice_entry.filter.epic_option.all')}
                </option>
                {Object.keys(props.epics).map((epicKey) => (
                    <option key={epicKey} value={epicKey}>
                        {props.epics[epicKey]}
                    </option>
                ))}
            </select>

            <label htmlFor={'versionFilter'}>{t('invoice_entry.filter.version')}</label>
            <select
                name={'versionFilter'}
                className={'form-control'}
                value={props.filterValues.versionFilter}
                onChange={props.handleChange}>
                <option value={''}>
                    {t('invoice_entry.filter.version_option.all')}
                </option>
                {Object.keys(props.versions).map((versionKey) => (
                    <option key={versionKey} value={versionKey}>
                        {props.versions[versionKey]}
                    </option>
                ))}
            </select>
        </Form.Group>
    );
};

InvoiceEntryJiraFilter.propTypes = {
    epics: PropTypes.object,
    filterValues: PropTypes.object.isRequired,
    handleChange: PropTypes.func.isRequired,
    handleStartDateChange: PropTypes.func.isRequired,
    handleEndDateChange: PropTypes.func.isRequired,
    t: PropTypes.func.isRequired,
    versions: PropTypes.object,
    workers: PropTypes.array,
    expenseCategories: PropTypes.object
};

export default withTranslation()(InvoiceEntryJiraFilter);
