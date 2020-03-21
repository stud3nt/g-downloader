import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes }  from "@angular/router";
import { DashboardComponent } from "./component/dashboard/dashboard.component";
import { ParserComponent } from "./component/parser/parser.component";
import { SettingsComponent } from "./component/settings/settings.component";
import { ListComponent as UsersListComponent } from "./component/users/list/list.component";
import { EditorComponent as UsersEditorComponent } from "./component/users/list/editor/editor.component";
import { GroupsComponent as UsersGroupsComponent } from "./component/users/groups/groups.component";
import { LoginComponent } from "./component/security/login/login.component";
import { TagsComponent } from "./component/lists/tags/tags.component";
import { CategoriesComponent } from "./component/lists/categories/categories.component";

@NgModule({
	imports: [
		CommonModule
	],
	exports: [ RouterModule ],
	declarations: []
})

export class AppRoutingModule { }

export const routes : Routes = [
	{
		path: '',
		component: DashboardComponent
	},
	{
		path: 'login',
		component: LoginComponent
	},
	{
		path: 'settings',
		component: SettingsComponent
	},
	{
		path: 'parsers/:parserName',
		component: ParserComponent
	},
	{
		path: 'parsers/:parserName/:nodeLevel',
		component: ParserComponent
	},
	{
		path: 'parsers/:parserName/:nodeLevel/:nodeIdentifier',
		component: ParserComponent
	},
	{
		path: 'users/list',
		component: UsersListComponent
	},
	{
		path: 'users/list/edit/:userToken',
		component: UsersEditorComponent
	},
	{
		path: 'users/groups',
		component: UsersGroupsComponent
	},
	{
		path: 'lists/tags',
		component: TagsComponent
	},
	{
		path: 'lists/categories',
		component: CategoriesComponent
	}
];
