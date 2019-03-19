import React, {Component} from 'react';
import Avatar from '@atlaskit/avatar';
import AddIcon from '@atlaskit/icon/glyph/add';
import FolderFilledIcon from '@atlaskit/icon/glyph/folder-filled';
import GraphLineIcon from '@atlaskit/icon/glyph/graph-line';
import QuestionCircleIcon from '@atlaskit/icon/glyph/question-circle';
import SearchIcon from '@atlaskit/icon/glyph/search';
import RoadmapIcon from '@atlaskit/icon/glyph/roadmap';
import Icon from '@atlaskit/icon';
import ItkLogo from '../components/ItkLogo';
import {gridSize as gridSizeFn} from '@atlaskit/theme';
import {
  GlobalNav,
  GroupHeading,
  Item,
  LayoutManager,
  MenuSection,
  NavigationProvider,
} from '@atlaskit/navigation-next';
import { Link } from 'react-router';
import store from '../redux/store';
import { fetchCurrentUserIfNeeded } from '../redux/actions';
import connect from 'react-redux/es/connect/connect';
import PropTypes from 'prop-types';

const gridSize = gridSizeFn();

class Navigation extends Component {
  constructor(props) {
    super(props);

    this.state = {
      shouldDisplayContainerNav: true,
      dialogOpen: false
    };
  }

  componentDidMount() {
    store.dispatch(fetchCurrentUserIfNeeded());
  }

  toggleContainerNav = () => {
    this.setState(state => ({
      shouldDisplayContainerNav: !state.shouldDisplayContainerNav,
    }));
  };

  ContainerNavigation = () => (
    <div data-webdriver-test-key="container-navigation">
      <MenuSection>
        {({className}) => (
          <div className={className}>
            <GroupHeading>ITK Jira</GroupHeading>

            <Link to={`/`}>
              <Item
                before={FolderFilledIcon}
                text="Project List"
                testKey="container-item-all-projects"
              />
            </Link>

            <Link to={`/statistics`}>
              <Item
                before={GraphLineIcon}
                text="Statistics"
                testKey="container-item-sprintplanning"
              />
            </Link>

            <Link to={`/planning`}>
              <Item
                before={RoadmapIcon}
                text="Planning"
                testKey="container-item-sprintplanning"
              />
            </Link>
          </div>
        )}
      </MenuSection>
    </div>
  );

  render() {
    const {shouldDisplayContainerNav} = this.state;

    const globalNavPrimaryItems = [
      {
        id: 'itk',
        icon: () => <Icon glyph={ItkLogo} label="ITK" size="large"/>,
        label: 'ITK',
      },
      {id: 'search', icon: SearchIcon, label: 'Search'},
      {id: 'create', icon: AddIcon, label: 'Add'},
    ];

    const globalNavSecondaryItems = [
      {
        id: '10-composed-navigation',
        icon: QuestionCircleIcon,
        label: 'Help',
        size: 'small',
      },
      {
        id: '10-composed-navigation-2',
        icon: () => (
          <Avatar
            borderColor="transparent"
            isActive={false}
            isHover={false}
            size="small"
            src={this.props.avatar}
          />
        ),
        label: 'Profile',
        size: 'small',
        href: `${this.props.jiraUrl}/people/${this.props.userUrl}`
      },
    ];

    return (
      <NavigationProvider>
        <LayoutManager
          globalNavigation={() => (<div data-webdriver-test-key="global-navigation">
            {
              this.props.avatar ? (
                <GlobalNav
                  primaryItems={globalNavPrimaryItems}
                  secondaryItems={globalNavSecondaryItems}
                />
              ) : (<div></div>)
            }
          </div>)}
          productNavigation={() => null}
          containerNavigation={
            shouldDisplayContainerNav ? this.ContainerNavigation : null
          }
        >
          <div
            data-webdriver-test-key="content"
            style={{padding: `${gridSize * 4}px ${gridSize * 5}px`}}
          >

          </div>
        </LayoutManager>
      </NavigationProvider>
    );
  }
}

Navigation.propTypes = {
  jiraUrl: PropTypes.string,
  avatar: PropTypes.string,
  userUrl: PropTypes.string,
  isFetching: PropTypes.bool
};

const mapStateToProps = state => {
  return {
    // @TODO: Get this from backend.
    jiraUrl: 'https://itkdev.atlassian.net',
    avatar: state.currentUser.currentUser.hasOwnProperty('avatarUrls') ? state.currentUser.currentUser.avatarUrls['16x16'] : '',
    userUrl: state.currentUser.currentUser.hasOwnProperty('accountId') ? state.currentUser.currentUser.accountId : '',
    isFetching: state.currentUser.isFetching
  };
};

export default connect(
  mapStateToProps
)(Navigation);
