import React, { Component } from 'react';
import styled from 'styled-components';
import PropTypes from 'prop-types';
import connect from 'react-redux/es/connect/connect';
import rest from '../redux/utils/rest';
import { Link } from 'react-router-dom';

const createRows = (projects) => {
  if (projects.data.data === undefined) {
    return [];
  }

  return projects.data.data.map((project, index) => ({
    rowKey: `row-${project.id}`,
    name: project.name,
    key: project.key,
    id: project.id,
    avatar: <img src={project.avatarUrls['16x16']} style={imageStyle}/>,
    link: <Link to={`/project/${project.id}`}>{project.name}</Link>
  }));
};

const imageStyle = {
  maxWidth: '20px'
};

const InputWrapper = styled.div`
  width: 50%;
  margin-bottom: 20px;
`;

class ProjectList extends Component {
  constructor(){
    super();
    this.state = {
      inputFilter: ''
    };

    this.onFilterChange = this.onFilterChange.bind(this)
  }

  componentDidMount() {
    const {dispatch} = this.props;
    dispatch(rest.actions.getProjects());
  }

  onFilterChange(event) {
    this.setState({
      inputFilter: event.target.value
    });
  }

  filterProjectRows() {
    return this.props.projectRows.filter(row => row.name.toLowerCase().search(
      this.state.inputFilter.toLowerCase()
    ) !== -1)
  }

  render () {
    const items = [];

    const projects = this.filterProjectRows();

    for (const [index, project] of Object.entries(projects)) {
      items.push(
        <li key={project.rowKey}>
          {project.avatar}
          {project.key}
          {project.id}
          {project.link}
        </li>
      );
    }

    const fetching = this.props.isFetching ? '...' : '';

    return (
      <div>
        <InputWrapper>
          <input
            type="text"
            placeholder="Enter the name of a project"
            value={this.state.inputFilter}
            onChange={this.onFilterChange}
          />
        </InputWrapper>

        <ul>
        {items}
        </ul>

        {fetching}
      </div>
    );
  }
}

ProjectList.propTypes = {
  projects: PropTypes.object,
  projectRows: PropTypes.array,
  isFetching: PropTypes.bool
};

const mapStateToProps = state => {
  let projectRows = createRows(state.projects);

  return {
    projects: state.projects,
    projectRows: projectRows,
    isFetching: state.projects.isFetching
  };
};

export default connect(
  mapStateToProps
)(ProjectList);
