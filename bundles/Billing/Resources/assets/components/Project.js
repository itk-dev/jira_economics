import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router-dom';
import PropTypes from 'prop-types';
import rest from '../redux/utils/rest';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';

const $ = require('jquery');

class Project extends Component {
  constructor(props) {
    super(props);
    this.handleCreateSubmit = this.handleCreateSubmit.bind(this);
    this.state = { invoiceName: '' };
  }

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(rest.actions.getProject({ id: `${this.props.match.params.projectId}` }));
    dispatch(rest.actions.getInvoices({ id: `${this.props.match.params.projectId}` }));
  }

  handleCreateSubmit = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;
    const projectId = this.props.match.params.projectId;
    // @TODO: look into getting this value from the event instead
    const name = $("#invoice-name").val();
    const invoiceData = {
      projectId,
      name
    };

    dispatch(rest.actions.createInvoice({}, {
      body: JSON.stringify(invoiceData)
    }));
  }

  // @TODO: Update list of invoices when state changes
  render() {
    if (this.props.project.data.name) {
      return (
        <ContentWrapper>
          <PageTitle breadcrumb={this.props.project.data.name + ' (' + this.props.project.data.jiraId + ')'}>
            Create new invoice
          </PageTitle>
          {this.props.invoices.data.data && this.props.invoices.data.data.map((item) =>
            <div key={item.jiraId}><Link to={`/project/${this.props.match.params.projectId}/${item.invoiceId}`}>Link til {item.name}</Link></div>
          )}

          <Form id="submitForm" onSubmit={this.handleCreateSubmit}>
            <Form.Group controlId="submitForm" >
              <Form.Label>Invoice name</Form.Label>
              <Form.Control type="text" placeholder="Enter name for invoice" />
              <Form.Text className="text-muted">
                The name should help you identify this Invoice later on.
              </Form.Text>
            </Form.Group>
            <Button variant="primary" type="submit" id="submit">
              Create new invoice
            </Button>
          </Form>

        </ContentWrapper>
      );
    }
    else {
      return (
        <ContentWrapper>
          <div className="spinner-border" style={{ width: '3rem', height: '3rem', role: 'status' }}>
            <span className="sr-only">Loading...</span>
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
