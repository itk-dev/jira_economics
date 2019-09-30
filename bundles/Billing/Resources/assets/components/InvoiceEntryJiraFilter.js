import React from 'react';
import DatePicker from 'react-datepicker';
import Form from 'react-bootstrap/Form';
import PropTypes from 'prop-types';
import { withTranslation } from 'react-i18next';
import Select from 'react-select';

const InvoiceEntryJiraFilter = (props) => {
    const { t } = props;

    const billedFilterOptions = [
        {
            value: 'not_billed',
            label: t('invoice_entry.filter.billed_option.not_billed')
        },
        {
            value: 'billed',
            label: t('invoice_entry.filter.billed_option.billed')
        }
    ];

    const workerFilterOptions = props.workers.map(worker => {
        return {
            value: worker,
            label: worker
        };
    });

    const epicFilterOptions = Object.keys(props.epics).map((epicKey) => {
        return {
            value: epicKey,
            label: props.epics[epicKey]
        };
    });

    const versionFilterOptions = Object.keys(props.versions).map((versionKey) => {
        return {
            value: versionKey,
            label: props.versions[versionKey]
        };
    });

    const categoryFilterOptions = Object.keys(props.categories).map((categoryKey) => {
        return {
            value: categoryKey,
            label: props.categories[categoryKey]
        };
    });

    const accountKeyFilterOptions = Object.keys(props.accountKeys).map((accountKey) => {
        return {
            value: accountKey,
            label: props.accountKeys[accountKey]
        };
    });

    return (
        <Form.Group>
            <label htmlFor={'startDateFilter'}>{t('invoice_entry.filter.start_date')}</label>
            <DatePicker name={'startDateFilter'} dateFormat={'dd/MM yyyy'} className={'form-control'} selected={props.filterValues.startDateFilter} isClearable onChange={props.handleStartDateChange} />

            <label htmlFor={'endDateFilter'}>{t('invoice_entry.filter.end_date')}</label>
            <DatePicker name={'endDateFilter'} dateFormat={'dd/MM yyyy'} className={'form-control'} selected={props.filterValues.endDateFilter} isClearable onChange={props.handleEndDateChange} />

            {!props.billedFilterDisable &&
                <div>
                    <label htmlFor={'billedFilter'}>{t('invoice_entry.filter.billed')}</label>
                    <Select
                        value={billedFilterOptions.filter(item => props.filterValues.billedFilter === item.value)}
                        name={'billedFilter'}
                        placeholder={t('invoice_entry.filter.billed_option.all')}
                        isSearchable={true}
                        isClearable={true}
                        ariaLabel={t('invoice_entry.filter.billed')}
                        onChange={(selectedOption) => props.handleChange('billedFilter', selectedOption ? selectedOption.value : '')}
                        options={billedFilterOptions}
                    />
                </div>
            }

            {props.workers.length > 0 &&
                <div>
                    <label htmlFor={'workerFilter'}>{t('invoice_entry.filter.worker')}</label>
                    <Select
                        value={workerFilterOptions.filter(item => props.filterValues.workerFilter === item.value)}
                        name={'workerFilter'}
                        isSearchable={true}
                        isClearable={true}
                        ariaLabel={t('invoice_entry.filter.worker')}
                        placeholder={t('invoice_entry.filter.worker_option.all')}
                        onChange={(selectedOption) => props.handleChange('workerFilter', selectedOption ? selectedOption.value : '')}
                        options={workerFilterOptions}
                    />
                </div>
            }

            {Object.keys(props.epics).length > 0 &&
                <div>
                    <label htmlFor={'epicFilter'}>{t('invoice_entry.filter.epic')}</label>
                    <Select
                        value={epicFilterOptions.filter(item => props.filterValues.epicFilter === item.value)}
                        name={'epicFilter'}
                        isSearchable={true}
                        isClearable={true}
                        ariaLabel={t('invoice_entry.filter.epic')}
                        placeholder={t('invoice_entry.filter.epic_option.all')}
                        onChange={(selectedOption) => props.handleChange('epicFilter', selectedOption ? selectedOption.value : '')}
                        options={epicFilterOptions}
                    />
                </div>
            }

            {Object.keys(props.versions).length > 0 &&
                <div>
                    <label htmlFor={'versionFilter'}>{t('invoice_entry.filter.version')}</label>
                    <Select
                        value={versionFilterOptions.filter(item => props.filterValues.versionFilter === item.value)}
                        name={'versionFilter'}
                        isSearchable={true}
                        isClearable={true}
                        ariaLabel={t('invoice_entry.filter.version')}
                        placeholder={t('invoice_entry.filter.version_option.all')}
                        onChange={(selectedOption) => props.handleChange('versionFilter', selectedOption ? selectedOption.value : '')}
                        options={versionFilterOptions}
                    />
                </div>
            }

            {Object.keys(props.categories).length > 0 &&
            <div>
                <label htmlFor={'categoryFilter'}>{t('invoice_entry.filter.category')}</label>
                <Select
                    value={categoryFilterOptions.filter(item => props.filterValues.categoryFilter === item.value)}
                    name={'categoryFilter'}
                    isSearchable={true}
                    isClearable={true}
                    ariaLabel={t('invoice_entry.filter.category')}
                    placeholder={t('invoice_entry.filter.category_option.all')}
                    onChange={(selectedOption) => props.handleChange('categoryFilter', selectedOption ? selectedOption.value : '')}
                    options={categoryFilterOptions}
                />
            </div>
            }

            {Object.keys(props.accountKeys).length > 0 &&
            <div>
                <label htmlFor={'accountKeyFilter'}>{t('invoice_entry.filter.account_key')}</label>
                <Select
                    value={accountKeyFilterOptions.filter(item => props.filterValues.accountKeyFilter === item.value)}
                    name={'accountKeyFilter'}
                    isSearchable={true}
                    isClearable={true}
                    ariaLabel={t('invoice_entry.filter.account_key')}
                    placeholder={t('invoice_entry.filter.account_key_option.all')}
                    onChange={(selectedOption) => props.handleChange('accountKeyFilter', selectedOption ? selectedOption.value : '')}
                    options={accountKeyFilterOptions}
                />
            </div>
            }
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
    categories: PropTypes.object,
    workers: PropTypes.array,
    expenseCategories: PropTypes.object,
    billedFilterDisable: PropTypes.bool,
    accountKeys: PropTypes.object
};

export default withTranslation()(InvoiceEntryJiraFilter);
