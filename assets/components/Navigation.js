import React, {Component} from 'react';
import FolderFilledIcon from '@atlaskit/icon/glyph/folder-filled';
import GraphLineIcon from '@atlaskit/icon/glyph/graph-line';
import RoadmapIcon from '@atlaskit/icon/glyph/roadmap';
import ItkLogo from '../components/ItkLogo';
import {gridSize as gridSizeFn} from '@atlaskit/theme';
import {
  GroupHeading,
  Item,
  LayoutManager,
  MenuSection,
  NavigationProvider,
} from '@atlaskit/navigation-next';
import { Link } from 'react-router';
import store from '../redux/store';
import connect from 'react-redux/es/connect/connect';
import PropTypes from 'prop-types';
import { DropdownItemGroup, DropdownItem } from '@atlaskit/dropdown-menu';
import GlobalNavigation from '@atlaskit/global-navigation';
import rest from '../redux/utils/rest';

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
    const {dispatch} = this.props;
    dispatch(rest.actions.getCurrentUser());
  }

  renderNavigation = () => (
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

  renderGlobalNavigation = () => {
    const UserDropdown = () => (
      <DropdownItemGroup title="User options">
        <DropdownItem><a href={this.props.jiraUrl + "/people/" + this.props.userUrl}>Open user in Jira</a></DropdownItem>
      </DropdownItemGroup>
    );

    // @TODO: Handle click events.
    return <GlobalNavigation
      productIcon={ItkLogo}
      productHref="/"
      onProductClick={() => console.log('product clicked')}
      onCreateClick={() => console.log('create clicked')}
      onSearchClick={() => console.log('search clicked')}
      onSettingsClick={() => console.log('settings clicked')}
      profileItems={UserDropdown}
      profileIconUrl={this.props.avatar}
    />
  };

  render() {
    return (
      <NavigationProvider>
        <LayoutManager
          globalNavigation={this.renderGlobalNavigation}
          productNavigation={() => null}
          containerNavigation={this.renderNavigation}
        >
          <div data-webdriver-test-key="content" style={{padding: `${gridSize * 4}px ${gridSize * 5}px`}}>
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
    avatar: state.currentUser.hasOwnProperty('avatarUrls') ? state.currentUser.avatarUrls['16x16'] : '',
    userUrl: state.currentUser.hasOwnProperty('accountId') ? state.currentUser.accountId : '',
    isFetching: state.currentUser.isFetching
  };
};

export default connect(
  mapStateToProps
)(Navigation);
