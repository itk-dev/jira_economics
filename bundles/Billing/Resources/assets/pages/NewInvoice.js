import React from 'react';
import PropTypes from 'prop-types';
import ProjectList from '../components/ProjectList';
import PageTitle from '../components/PageTitle';
import ContentWrapper from '../components/ContentWrapper';
import { withTranslation } from 'react-i18next';

function NewInvoice (props) {
    const { t } = props;

    return (
        <ContentWrapper>
            <PageTitle breadcrumb={t('invoice.new')}>
                {t('invoice.choose_project')}
            </PageTitle>
            <ProjectList/>
        </ContentWrapper>
    );
}

NewInvoice.propTypes = {
    t: PropTypes.func.isRequired
};

export default withTranslation()(NewInvoice);
