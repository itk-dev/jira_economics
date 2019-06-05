import React  from 'react';
import ProjectList from '../components/ProjectList';
import PageTitle from '../components/PageTitle';
import ContentWrapper from '../components/ContentWrapper';

export default function NewInvoice(props) {
  return (
    <ContentWrapper>
      <p className="text-muted">Ny faktura</p>
      <PageTitle>Vælg projekt</PageTitle>
      <hr/>
      <ProjectList/>
    </ContentWrapper>
  )
}
