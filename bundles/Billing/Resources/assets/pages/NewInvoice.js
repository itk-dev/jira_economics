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
import Select from 'react-select';
import Bus from '../modules/Bus';

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
            selectedCustomerAccount: null
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
        const customerAccountId = this.state.selectedCustomerAccount ? this.state.selectedCustomerAccount.value : '';

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
                Bus.emit('flash', ({ message: JSON.stringify(reason), type: 'danger' }));
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
                                <Select
                                    value={this.state.selectedCustomerAccount}
                                    name={'selectedCustomerAccount'}
                                    placeholder={t('invoice.form.select_account')}
                                    isClearable={true}
                                    isSearchable={true}
                                    aria-label={t('new_invoice.select_customer_account')}
                                    onChange={
                                        selectedOption => {
                                            this.setState({ selectedCustomerAccount: selectedOption });
                                        }
                                    }
                                    options={
                                        Object.keys(this.props.accounts.data).map((keyName) => {
                                            let label = this.props.accounts.data[keyName].name;

                                            label = label + ' - ' + (this.props.accounts.data[keyName].customer ? this.props.accounts.data[keyName].customer.key : t('new_invoice.no_customer'));

                                            if (!this.props.accounts.data[keyName].category) {
                                                label = label + ' - ' + t('new_invoice.no_category');
                                            } else {
                                                if (this.props.accounts.data[keyName].category.name === 'INTERN') {
                                                    label = label + ' - PSP: ' + this.props.accounts.data[keyName].key;
                                                } else {
                                                    label = label + ' - EAN: ' + this.props.accounts.data[keyName].key;
                                                }
                                            }

                                            return {
                                                'value': this.props.accounts.data[keyName].id,
                                                'label': label
                                            };
                                        })
                                    }
                                />
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
