import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProject, fetchInvoices } from '../redux/actions';
import PropTypes from 'prop-types';
import Spinner from '@atlaskit/spinner';
import rest from '../redux/utils/rest';

class Project extends Component {
  componentDidMount() {
    const {dispatch} = this.props;
    dispatch(rest.actions.project({id: `${this.props.params.projectId}`}));
    store.dispatch(fetchInvoices(this.props.params.projectId));
  }
  render () {
    if (this.props.project.data.name) {
      return (
        <ContentWrapper>
          <PageTitle>
            {this.props.project.data.name + ' (' + this.props.project.data.jiraId + ')'}
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
  invoices: PropTypes.array,
  project: PropTypes.object,
  dispatch: PropTypes.func.isRequired
};

const mapStateToProps = state => {
  return {
    invoices: state.invoices.invoices,
    project: state.project
  };
};

export default connect(
  mapStateToProps
)(Project);
