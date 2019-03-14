import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import styled from 'styled-components';
import DynamicTable from '@atlaskit/dynamic-table';
import { head, createRows } from '../components/ProjectsList';
import connect from 'react-redux/es/connect/connect';
import PropTypes from 'prop-types';

const Wrapper = styled.div`
  min-width: 400px;
`;

class HomePage extends Component {
  render () {
    return (
      <ContentWrapper>
        <PageTitle>ITK Jira projects</PageTitle>
        <Wrapper>
          <DynamicTable
            head={head}
            rows={this.props.projectRows}
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

HomePage.propTypes = {
  projects: PropTypes.array,
  isFetching: PropTypes.bool
};

const mapStateToProps = state => {
  let projectRows = createRows(state.projects.projects);

  return {
    projectRows: projectRows,
    isFetching: state.projects.isFetching
  }
};

export default connect(
  mapStateToProps
)(HomePage);
