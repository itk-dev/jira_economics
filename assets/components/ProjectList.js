import React, { Component } from 'react';
import styled from 'styled-components';
import BillingFilledIcon from '@atlaskit/icon/glyph/billing-filled';
import MediaServicesPdfIcon from '@atlaskit/icon/glyph/media-services/pdf';
import Button, { ButtonGroup } from '@atlaskit/button';
import Icon from '@atlaskit/icon';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProjectsIfNeeded } from '../redux/actions';
import DynamicTable from '@atlaskit/dynamic-table/dist/esm/components/Stateful';
import PropTypes from 'prop-types';
import connect from 'react-redux/es/connect/connect';

const createHead = (withWidth) => {
  return {
    cells: [
      {
        key: 'avatar',
        content: '',
        isSortable: false,
        width: withWidth ? 2 : undefined,
      },
      {
        key: 'name',
        content: 'Name',
        isSortable: true,
        width: withWidth ? 25 : undefined,
      },
      {
        key: 'shortname',
        content: 'Shortname',
        shouldTruncate: true,
        isSortable: true,
        width: withWidth ? 15 : undefined,
      },
      {
        key: 'tools',
        content: 'Tools',
        width: withWidth ? 25 : undefined,
      },
    ],
  };
};
const createRows = (projects) => {
  if (projects === undefined) {
    return [];
  }

  return projects.map((project, index) => ({
    key: `row-${project.id}`,
    values: {
      name: project.name,
      key: project.key,
      id: project.id
    },
    cells: [
      {
        key: `avatar-${project.id}`,
        content: (
          <img src={project.avatarUrls['16x16']} style={imageStyle}/>
        )
      },
      {
        key: `name-${project.id}`,
        content: (
          <NameWrapper>
            <a href={project.url}>{project.name}</a>
          </NameWrapper>
        ),
      },
      {
        key: `shortname-${project.id}`,
        content: project.key,
      },
      {
        key: `tools-${project.id}`,
        content: (
          <ButtonGroup>
            <Link to={`/project/${project.id}`}>
              <Button iconBefore={<Icon glyph={BillingFilledIcon} label="Invoices" size="medium"/>}>
                Invoices
              </Button>
            </Link>
            <Button href="sprint_report/project/" iconBefore={<Icon glyph={MediaServicesPdfIcon} label="Sprint report" size="medium"/>}>
              Sprint report
            </Button>
          </ButtonGroup>
        ),
      },
    ],
  }));
};

const head = createHead(true);

const NameWrapper = styled.span`
  display: flex;
  align-items: center;
`;

const imageStyle = {
  maxWidth: '20px'
};

const Wrapper = styled.div`
  min-width: 400px;
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
    store.dispatch(fetchProjectsIfNeeded());
  }

  onFilterChange(event) {
    this.setState({
      inputFilter: event.target.value
    });
  }

  filterProjectRows() {
    return this.props.projectRows.filter(row => row.values.name.toLowerCase().search(
      this.state.inputFilter.toLowerCase()
    ) !== -1)
  }

  render () {
    return (
      <Wrapper>
        <input
          type="text"
          className="form-control form-control-lg"
          placeholder="Search"
          value={this.state.inputFilter}
          onChange={this.onFilterChange}
        />

        <DynamicTable
          head={head}
          rows={this.filterProjectRows()}
          rowsPerPage={20}
          defaultPage={1}
          loadingSpinnerSize="large"
          isLoading={this.props.isFetching}
          isFixedSize
          defaultSortKey="name"
          defaultSortOrder="ASC"
        />
      </Wrapper>
    );
  }
}

ProjectList.propTypes = {
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
)(ProjectList);
