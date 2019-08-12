import React from 'react';
import ProjectList from '../components/ProjectList';
import PageTitle from '../components/PageTitle';
import ContentWrapper from '../components/ContentWrapper';

export default function NewInvoice(props) {
  return (
    <ContentWrapper>
      <PageTitle breadcrumb='Ny faktura'>
        Vælg projekt
      </PageTitle>
      <ProjectList />
    </ContentWrapper>
  )
}
