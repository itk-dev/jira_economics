import React  from 'react';
import ProjectList from '../components/ProjectList';
import PageTitle from '../components/PageTitle';
import ContentWrapper from '../components/ContentWrapper';

export default function HomePage(props) {
  return (
    <ContentWrapper>
      <PageTitle>ITK Jira projects</PageTitle>
      <ProjectList/>
    </ContentWrapper>
  )
}
