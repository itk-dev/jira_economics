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
    constructor () {
        super();
        this.state = {
            inputFilter: '',
            showModal: false,
            selectedProject: 0
        };

        this.onFilterChange = this.onFilterChange.bind(this);
        this.handleModalShow = this.handleModalShow.bind(this);
        this.handleModalCancel = this.handleModalCancel.bind(this);
        this.handleModalCreate = this.handleModalCreate.bind(this);

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
        this.setState({ showModal: true, selectedProject: projectId });
    }

    handleModalCreate () {
        event.preventDefault();
        const { dispatch } = this.props;
        const name = this.textInput.current.value;
        const projectId = this.state.selectedProject;
        const invoiceData = {
            name,
            projectId
        };
        dispatch(rest.actions.createInvoice({}, {
            body: JSON.stringify(invoiceData)
        }))
            .then((response) => {
                this.setState({ showModal: false });
                this.props.history.push(`/project/${projectId}/${response.invoiceId}`);
            })
            .catch((reason) => {
                this.setState({ showModal: false });
                console.log('isCanceled', reason.isCanceled);
            });
        // @TODO: verify that invoice creation was successful
    }

    displayProjects (items) {
        if (this.props.projects.loading) {
            return (
                <ContentWrapper>
                    <div className="spinner-border"
                        style={{
                            width: '3rem',
                            height: '3rem',
                            role: 'status'
                        }}>
                        <span className="sr-only">Loading...</span>
                    </div>
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

    render () {
        const items = [];

        const projects = this.filterProjectRows();

        for (const [index, project] of Object.entries(projects)) {
            items.push(
                <ListGroup.Item key={project.rowKey} id={project.id} action
                    onClick={() => this.handleModalShow(project.id)}>
                    <span className="mr-2">{project.avatar}</span>
                    <span className="mr-2 lead d-inline">{project.name}</span>
                    <span className="text-muted">{project.key}</span>
                </ListGroup.Item>
            );
        }

        // @TODO: Center modal text

        return (
            <div>
                <Form>
                    <Form.Group controlId="formBasicEmail">
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

                <Modal show={this.state.showModal}
                    onHide={this.handleModalCancel}>
                    <Modal.Header>
                        <Modal.Title>Create Invoice</Modal.Title>
                    </Modal.Header>
                    <Form>
                        <Form.Group controlId="newInvoiceForm">
                            <Form.Label>Invoice name</Form.Label>
                            <Form.Control ref={this.textInput} type="text"
                                placeholder="Enter new invoice name"></Form.Control>
                        </Form.Group>
                    </Form>
                    <Modal.Footer>
                        <Button variant="secondary"
                            onClick={this.handleModalCancel}>
                            Cancel
                        </Button>
                        <Button variant="primary"
                            onClick={this.handleModalCreate}>
                            Create
                        </Button>
                    </Modal.Footer>
                </Modal>
            </div>
        );
    }
}

ProjectList.propTypes = {
    projects: PropTypes.object,
    projectRows: PropTypes.array
};

const mapStateToProps = state => {
    let projectRows = createRows(state.projects);

    return {
        projects: state.projects,
        projectRows: projectRows
    };
};

export default withRouter(connect(
    mapStateToProps
)(ProjectList));
