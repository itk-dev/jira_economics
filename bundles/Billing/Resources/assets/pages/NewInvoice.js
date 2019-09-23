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
import Spinner from '../components/Spinner';
import { withTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';

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

class NewInvoice extends Component {
    constructor (props) {
        super(props);
        this.state = {
            accounts: {},
            inputFilter: '',
            showModal: false,
            selectedProject: null,
            selectedCustomerAccount: ''
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
        const customerAccountId = this.state.selectedCustomerAccount;
        const invoiceData = {
            name: name,
            projectId: projectId,
            customerAccountId: customerAccountId
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

    accountOptions = () => {
        return Object.keys(this.props.accounts.data)
            .map((keyName) => (
                this.props.accounts.data.hasOwnProperty(keyName) &&
                <option
                    key={this.props.accounts.data[keyName].id}
                    value={this.props.accounts.data[keyName].id}>
                    {this.props.accounts.data[keyName].category.name === 'INTERN'
                        ? this.props.accounts.data[keyName].name + ': ' + this.props.accounts.data[keyName].customer.key + ' - PSP: ' + this.props.accounts.data[keyName].key
                        : this.props.accounts.data[keyName].name + ': ' + this.props.accounts.data[keyName].customer.key + ' - EAN: ' + this.props.accounts.data[keyName].key}
                </option>
            ));
    };

    render () {
        const { t } = this.props;

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

        return (
            <ContentWrapper>
                <PageTitle breadcrumb={t('invoice.new')}>
                    {t('invoice.choose_project')}
                </PageTitle>

                <Form>
                    <Form.Group>
                        <Form.Label
                            className="sr-only">{t('new_invoice.filter_title')}</Form.Label>
                        <Form.Control
                            type="text"
                            placeholder={t('new_invoice.filter_title_placeholder')}
                            value={this.state.inputFilter}
                            onChange={this.onFilterChange}
                        />
                    </Form.Group>
                </Form>

                {this.displayProjects(items)}

                <Modal show={this.state.showModal} onHide={this.handleModalCancel}>
                    <Form onSubmit={this.handleSubmit.bind(this)}>
                        <Modal.Header>
                            <Modal.Title>{t('new_invoice.create')}</Modal.Title>
                        </Modal.Header>
                        {!this.props.accounts.loading && this.props.accounts.hasOwnProperty('data') &&
                            <Form.Group>
                                <Form.Label>{t('new_invoice.form.name')}</Form.Label>
                                <Form.Control ref={this.textInput} type="text" placeholder={t('new_invoice.form.name_placeholder')}>
                                </Form.Control>
                                <Form.Label>{t('new_invoice.select_customer_account')}</Form.Label>
                                <Form.Control as="select" onChange={this.handleChange.bind(this)} name={'selectedCustomerAccount'}>
                                    <option value=''> </option>
                                    {this.accountOptions()}
                                </Form.Control>
                            </Form.Group>
                        }
                        {(this.props.accounts.loading || !this.props.accounts.hasOwnProperty('data')) &&
                            <ContentWrapper>
                                <Spinner/>
                            </ContentWrapper>
                        }
                        <Modal.Footer>
                            <Button variant="secondary" onClick={this.handleModalCancel.bind(this)}>
                                {t('common.modal.cancel')}
                            </Button>
                            <input type="submit" value={t('common.modal.create')} className={'btn btn-primary'} />
                        </Modal.Footer>
                    </Form>
                </Modal>
            </ContentWrapper>
        );
    }
}

NewInvoice.propTypes = {
    accounts: PropTypes.object,
    projects: PropTypes.object,
    projectRows: PropTypes.array,
    dispatch: PropTypes.func.isRequired,
    history: PropTypes.object,
    t: PropTypes.func.isRequired
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
)(withTranslation()(NewInvoice)));
