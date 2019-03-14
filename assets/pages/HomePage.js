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
  constructor(){
    super();
    this.state = {
      inputFilter: ''
    };

    this.onFilterChange = this.onFilterChange.bind(this)
  }

  onFilterChange(event) {
    this.setState({
      inputFilter: event.target.value
    });
  }

  render () {
    return (
      <ContentWrapper>
        <PageTitle>ITK Jira projects</PageTitle>

        <input
          type="text"
          className="form-control form-control-lg"
          placeholder="Search"
          value={this.state.inputFilter}
          onChange={this.onFilterChange}
        />

        <Wrapper>
          {(!this.props.isFetching ?
            (
              <DynamicTable
                head={head}
                rows={this.props.projectRows.filter(row => row.values.name.toLowerCase().search(
                  this.state.inputFilter.toLowerCase()
                ) !== -1)}
                rowsPerPage={10}
                defaultPage={1}
                loadingSpinnerSize="large"
                isLoading={false}
                isFixedSize
                defaultSortKey="name"
                defaultSortOrder="ASC"
              />
            ) :
            (<div>Loading...</div>)
          )}
        </Wrapper>
      </ContentWrapper>
    );
  }
}

HomePage.propTypes = {
  projects: PropTypes.array,
  projectRows: PropTypes.array,
  isFetching: PropTypes.bool
};

const mapStateToProps = state => {
  let projectRows = createRows(state.projects.projects);

  return {
    projects: state.projects.projects,
    projectRows: projectRows,
    isFetching: state.projects.isFetching
  };
};

export default connect(
  mapStateToProps
)(HomePage);
