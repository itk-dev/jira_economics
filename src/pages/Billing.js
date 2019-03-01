import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import styled from 'styled-components';
import DynamicTable from '@atlaskit/dynamic-table';
import { head, rows } from '../components/ProjectsList';
import { BreadcrumbsStateless, BreadcrumbsItem } from '@atlaskit/breadcrumbs';
import PageHeader from '@atlaskit/page-header';

const Wrapper = styled.div`
  min-width: 400px;
`;

const breadcrumbs = (
  <BreadcrumbsStateless onExpand={() => {}}>
    <BreadcrumbsItem text="Some project" key="Some project" />
  </BreadcrumbsStateless>
);

export default class Billing extends Component {
  render() {
    return (
      <ContentWrapper>
      <PageTitle>Billing</PageTitle>
      <PageHeader breadcrumbs={breadcrumbs}>
        Invoices
      </PageHeader>

      <p>Show list of invoices</p>

      {/* <Wrapper>
        <DynamicTable
          head={head}
          rows={rows}
          rowsPerPage={10}
          defaultPage={1}
          loadingSpinnerSize="large"
          isLoading={false}
          isFixedSize
          defaultSortKey="term"
          defaultSortOrder="ASC"
          onSort={() => console.log('onSort')}
          onSetPage={() => console.log('onSetPage')}
        />
      </Wrapper> */}
      </ContentWrapper>
      );
    }
  }
