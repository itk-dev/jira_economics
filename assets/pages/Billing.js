import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import styled from 'styled-components';
import DynamicTable from '@atlaskit/dynamic-table';
import { createRows, head, rows } from '../components/ProjectsList';
import { BreadcrumbsStateless, BreadcrumbsItem } from '@atlaskit/breadcrumbs';
import PageHeader from '@atlaskit/page-header';
import connect from 'react-redux/es/connect/connect';

const Wrapper = styled.div`
  min-width: 400px;
`;

const breadcrumbs = (
  <BreadcrumbsStateless onExpand={() => {}}>
    <BreadcrumbsItem text="Some project" key="Some project"/>
  </BreadcrumbsStateless>
);

export class Billing extends Component {
  render () {
    return (
      <ContentWrapper>
        <PageTitle>Billing</PageTitle>
        <PageHeader breadcrumbs={breadcrumbs}>
          Invoices
        </PageHeader>

        <p>Show list of invoices</p>

        <p>{this.props.params.projectId}</p>

      </ContentWrapper>
    );
  }
}

const mapStateToProps = state => {
  return {
  };
};

export default connect(
  mapStateToProps
)(Billing);
