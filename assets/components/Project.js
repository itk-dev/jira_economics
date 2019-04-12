import React, { Component } from 'react';
import ContentWrapper from '../components/ContentWrapper';
import PageTitle from '../components/PageTitle';
import connect from 'react-redux/es/connect/connect';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchProject, fetchInvoices } from '../redux/actions';
import PropTypes from 'prop-types';
import Button from '@atlaskit/button';
import Form, {Field} from '@atlaskit/form';
import Spinner from '@atlaskit/spinner';
import TextField from '@atlaskit/field-text';
import rest from '../redux/utils/rest';

class Project extends Component {
  constructor(props) {
    super(props);

    this.handleCreateSubmit = this.handleCreateSubmit.bind(this);
    this.state = { invoiceName: '' };
  }
  componentDidMount() {
    const {dispatch} = this.props;
    dispatch(rest.actions.getProject({id: `${this.props.params.projectId}`}));
    dispatch(rest.actions.getInvoices({id: `${this.props.params.projectId}`}));
  }
  handleCreateSubmit = (e) => {
    const {dispatch} = this.props;
    const projectId = this.props.params.projectId;
    const name = e.invoiceName;
    const invoiceData = {
      projectId,
      name
    }
    dispatch(rest.actions.createInvoice({}, {
      body: JSON.stringify(invoiceData)
    }));
  }
  render () {
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
            <Form onSubmit={this.handleCreateSubmit}>
                {({ formProps }) => (
                  <form {...formProps} name="submit-create-form">
                    <Field name="invoiceName" defaultValue={this.state.invoiceName} label="Enter invoice name for new invoice" isRequired>
                      {({ fieldProps}) => <TextField {...fieldProps} />}
                    </Field>
                    <Button type="submit" appearance="primary">Submit new invoice</Button>
                  </form>
                )}
            </Form>
          </div>
        </ContentWrapper>
      );
    }
    else {
      return (<ContentWrapper><Spinner size="large"/></ContentWrapper>);
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
