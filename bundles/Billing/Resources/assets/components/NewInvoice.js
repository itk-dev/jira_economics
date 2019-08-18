import React from 'react';
import ProjectList from '../components/ProjectList';
import PageTitle from '../components/PageTitle';
import ContentWrapper from '../components/ContentWrapper';
import { useTranslation } from 'react-i18next';

function NewInvoice (props) {
    const { t } = useTranslation();

    return (
        <ContentWrapper>
            <PageTitle breadcrumb={t('invoice.new')}>
                {t('invoice.choose_project')}
            </PageTitle>
            <ProjectList/>
        </ContentWrapper>
    );
}

export default NewInvoice;
