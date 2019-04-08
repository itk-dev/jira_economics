import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProject, fetchInvoices } from '../redux/actions';
import PropTypes from 'prop-types';
import Spinner from '@atlaskit/spinner';

class Project extends Component {
  componentDidMount() {
    store.dispatch(fetchProject(this.props.params.projectId));
    store.dispatch(fetchInvoices(this.props.params.projectId));
  }
  render () {
    if (this.props.selectedProject.name) {
      return (
        <ContentWrapper>
          <PageTitle>
            {this.props.selectedProject.name + ' (' + this.props.selectedProject.jiraId + ')'}
          </PageTitle>

          {this.props.invoices && this.props.invoices.map((item) =>
            <div key={item.id}><Link to={`/project/${this.props.params.projectId}/${item.id}`}>Link til {item.name}</Link></div>
          )}
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large"/></ContentWrapper>);
    }
  }
}

Project.propTypes = {
  selectedProject: PropTypes.object,
  invoices: PropTypes.array
};

const mapStateToProps = state => {
  return {
    selectedProject: state.selectedProject.selectedProject,
    invoices: state.invoices.invoices
  };
};

export default connect(
  mapStateToProps
)(Project);
