import React from 'react';
import { withTranslation } from 'react-i18next';
import PropTypes from 'prop-types';

const Spinner = (props) => {
    const { t } = props;

    return (
        <div className="spinner-border" style={{
            width: '3rem',
            height: '3rem',
            role: 'status'
        }}>
            <span className="sr-only">{t('spinner.loading')}</span>
        </div>
    );
};

Spinner.propTypes = {
    t: PropTypes.func.isRequired
};

export default withTranslation()(Spinner);
