import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import styled from 'styled-components';
import DynamicTable from '@atlaskit/dynamic-table';
import { head, rows } from '../components/ProjectsList';

const Wrapper = styled.div`
  min-width: 400px;
`;
export default class HomePage extends Component {
  render() {
    return (
      <ContentWrapper>
      <PageTitle>ITK Jira projects</PageTitle>
      <Wrapper>
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
      </Wrapper>
      </ContentWrapper>
      );
    }
  }
