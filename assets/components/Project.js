import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProject, fetchInvoices } from '../redux/actions';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';

const $ = require('jquery');

class Project extends Component {
  constructor(props) {
    super(props);
    this.handleCreateSubmit = this.handleCreateSubmit.bind(this);
    this.state = { invoiceName: '' };
  }
  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(rest.actions.getProject({ id: `${this.props.params.projectId}` }));
    dispatch(rest.actions.getInvoices({ id: `${this.props.params.projectId}` }));
  }
  handleCreateSubmit = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;
    const projectId = this.props.params.projectId;
    // @TODO: look into getting this value from the event instead
    const name = $("#invoice-name").val();
    const invoiceData = {
      projectId,
      name
    }
    dispatch(rest.actions.createInvoice({}, {
      body: JSON.stringify(invoiceData)
    }));
  }
  // @TODO: Update list of invoices when state changes
  render() {
    if (this.props.project.data.name) {
      return (
        <ContentWrapper>
          <PageTitle>
            {this.props.project.data.name + ' (' + this.props.project.data.jiraId + ')'}
          </PageTitle>

          {this.props.invoices.data.data && this.props.invoices.data.data.map((item) =>
            <div key={item.id}><Link to={`/project/${this.props.params.projectId}/${item.id}`}>Link til {item.name}</Link></div>
          )}
          <div>Create new invoice</div>
          <div>
            <form id="submitForm" onSubmit={this.handleCreateSubmit}>
              <div id="formGroup" className="form-group">
                <label htmlFor="input-new-invoice-name">Enter invoice name for new invoice</label>
                <input
                  type="text"
                  name="invoiceName"
                  className="form-control"
                  id="invoice-name"
                  aria-describedby="invoiceName"
                  placeholder="Enter invoice name">
                </input>
              </div>
              <button type="submit" className="btn btn-primary" id="submit">Submit new invoice</button>
            </form>
          </div>
        </ContentWrapper>
      );
    }
    else {
      return (
        <ContentWrapper>
          <div class="spinner-border" style={{ width: '3rem', height: '3rem', role: 'status' }}>
            <span class="sr-only">Loading...</span>
          </div>
        </ContentWrapper>
      );
    }
  }
}

Project.propTypes = {
  invoices: PropTypes.object,
  project: PropTypes.object,
  dispatch: PropTypes.func.isRequired
};

const mapStateToProps = state => {
  return {
    invoices: state.invoices,
    project: state.project
  };
};

export default connect(
  mapStateToProps
)(Project);
