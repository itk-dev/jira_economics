import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';
import PropTypes from 'prop-types';
import connect from 'react-redux/es/connect/connect';
import rest from '../redux/utils/rest';
import ListGroup from 'react-bootstrap/ListGroup';
import Form from 'react-bootstrap/Form';
import Modal from 'react-bootstrap/Modal';
import Button from 'react-bootstrap/Button';
import ContentWrapper from '../components/ContentWrapper';
import Spinner from './Spinner';

const createRows = (projects) => {
    if (projects.data.data === undefined) {
        return [];
    }

    return projects.data.data.map((project, index) => ({
        rowKey: `row-${project.id}`,
        name: project.name,
        key: project.key,
        id: project.id,
        avatar: <img src={project.avatarUrls['16x16']} style={imageStyle}/>
    }));
};

const imageStyle = {
    maxWidth: '20px'
};

class ProjectList extends Component {
    constructor (props) {
        super(props);
        this.state = {
            accounts: {},
            inputFilter: '',
            showModal: false,
            selectedProject: null,
            selectedAccount: ''
        };

        this.onFilterChange = this.onFilterChange.bind(this);
        this.handleModalShow = this.handleModalShow.bind(this);
        this.handleModalCancel = this.handleModalCancel.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);

        this.textInput = React.createRef();
    }

    componentDidMount () {
        const { dispatch } = this.props;
        dispatch(rest.actions.getProjects());
    }

    onFilterChange (event) {
        this.setState({
            inputFilter: event.target.value
        });
    }

    filterProjectRows () {
        return this.props.projectRows.filter(row => row.name.toLowerCase()
            .search(
                this.state.inputFilter.toLowerCase()
            ) !== -1);
    }

    handleModalCancel () {
        this.setState({ showModal: false });
    }

    handleModalShow (projectId) {
        const { dispatch } = this.props;
        this.setState({ showModal: true, selectedProject: projectId });
        dispatch(rest.actions.getProjectAccounts({ id: projectId }));
    }

    handleSubmit (event) {
        event.preventDefault();

        const { dispatch } = this.props;
        const name = this.textInput.current.value;
        const projectId = this.state.selectedProject;
        const accountId = this.state.selectedAccount;
        const invoiceData = {
            name: name,
            projectId: projectId,
            accountId: accountId
        };

        // Create the new invoice.
        dispatch(rest.actions.createInvoice({}, {
            body: JSON.stringify(invoiceData)
        }))
            .then((response) => {
                this.setState({ showModal: false });
                this.props.history.push(`/project/${projectId}/${response.id}`);
            })
            .catch((reason) => {
                this.setState({ showModal: false });
                console.log('isCanceled', reason);
            });
    }

    displayProjects (items) {
        if (this.props.projects.loading) {
            return (
                <ContentWrapper>
                    <Spinner/>
                </ContentWrapper>
            );
        } else {
            return (
                <ListGroup>
                    {items}
                </ListGroup>
            );
        }
    }

    handleChange (event) {
        let fieldName = event.target.name;
        let fieldVal = event.target.value;
        this.setState({ ...this.state, [fieldName]: fieldVal });
    }

    render () {
        const items = [];

        const projects = this.filterProjectRows();

        for (const [, project] of Object.entries(projects)) {
            items.push(
                <ListGroup.Item key={project.rowKey} id={project.id} action onClick={() => this.handleModalShow(project.id)}>
                    <span className="mr-2">{project.avatar}</span>
                    <span className="mr-2 lead d-inline">{project.name}</span>
                    <span className="text-muted">{project.key}</span>
                </ListGroup.Item>
            );
        }

        // @TODO: Styling. Center modal text
        return (
            <div>
                <Form>
                    <Form.Group>
                        <Form.Label
                            className="sr-only">Project filter</Form.Label>
                        <Form.Control
                            type="text"
                            placeholder="Find project"
                            value={this.state.inputFilter}
                            onChange={this.onFilterChange}
                        />
                    </Form.Group>
                </Form>

                {this.displayProjects(items)}

                <Modal show={this.state.showModal} onHide={this.handleModalCancel}>
                    <Form onSubmit={this.handleSubmit.bind(this)}>
                        <Modal.Header>
                            <Modal.Title>Create Invoice</Modal.Title>
                        </Modal.Header>
                        <Form.Group>
                            <Form.Label>Invoice name</Form.Label>
                            <Form.Control ref={this.textInput} type="text" placeholder="Enter new invoice name">
                            </Form.Control>
                            <Form.Label>Select Account</Form.Label>
                            <Form.Control as="select" onChange={this.handleChange.bind(this)} name={'selectedAccount'}>
                                <option value=''> </option>
                                {this.props.accounts !== {} && Object.keys(this.props.accounts.data)
                                    .map((keyName) => (
                                        this.props.accounts.data.hasOwnProperty(keyName) &&
                                        <option
                                            key={this.props.accounts.data[keyName].id} value={this.props.accounts.data[keyName].id}>{this.props.accounts.data[keyName].name}</option>
                                    ))}
                            </Form.Control>
                        </Form.Group>
                        <Modal.Footer>
                            <Button variant="secondary" onClick={this.handleModalCancel.bind(this)}>
                                Cancel
                            </Button>
                            <input type="submit" value="Submit" className={'btn btn-primary'} />
                        </Modal.Footer>
                    </Form>
                </Modal>
            </div>
        );
    }
}

ProjectList.propTypes = {
    accounts: PropTypes.object,
    projects: PropTypes.object,
    projectRows: PropTypes.array,
    dispatch: PropTypes.func.isRequired,
    history: PropTypes.object
};

const mapStateToProps = state => {
    let projectRows = createRows(state.projects);

    return {
        accounts: state.accounts,
        projects: state.projects,
        projectRows: projectRows
    };
};

export default withRouter(connect(
    mapStateToProps
)(ProjectList));
