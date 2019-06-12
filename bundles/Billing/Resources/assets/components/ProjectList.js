import React, { Component } from 'react';
import PropTypes from 'prop-types';
import connect from 'react-redux/es/connect/connect';
import rest from '../redux/utils/rest';
import ListGroup from 'react-bootstrap/ListGroup';
import Form from 'react-bootstrap/Form';

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
    linkUrl: `/billing/project/${project.id}`
  }));
};

const imageStyle = {
  maxWidth: '20px'
};

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
        <ListGroup.Item key={project.rowKey} id={project.id} action href={project.linkUrl}>
          <span className="mr-2">{project.avatar}</span>
          <span className="mr-2 lead d-inline">{project.name}</span>
          <span className="text-muted">{project.key}</span>
        </ListGroup.Item>
      );
    }

    // TODO: Add spinner
    const fetching = this.props.isFetching ? 'Loading ...' : '';

    return (
      <div>
        <Form>
          <Form.Group controlId="formBasicEmail">
            <Form.Label className="sr-only">Project filter</Form.Label>
            <Form.Control
              type="text"
              placeholder="Find project"
              value={this.state.inputFilter}
              onChange={this.onFilterChange}
            />
          </Form.Group>
        </Form>

        <ListGroup>
        {items}
        </ListGroup>

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
