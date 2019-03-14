import React, {Component} from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import {BreadcrumbsStateless, BreadcrumbsItem} from '@atlaskit/breadcrumbs';
import PageHeader from '@atlaskit/page-header';

const breadcrumbs = (
  <BreadcrumbsStateless onExpand={() => {}}>
    <BreadcrumbsItem text="Some project" key="Some project"/>
  </BreadcrumbsStateless>
);

export default class Statistics extends Component {
  render() {
    return (
      <ContentWrapper>
        <PageTitle>Statistics</PageTitle>
        <PageHeader breadcrumbs={breadcrumbs}>
          Invoices
        </PageHeader>
        <p>Show statistics</p>
      </ContentWrapper>
    );
  }
}
