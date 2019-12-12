import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from "@angular/forms";

import { IndexComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { RouterModule } from "@angular/router";
import { routes } from "./app-routing.module";
import { HttpClientModule } from "@angular/common/http";
import { DashboardComponent } from './component/dashboard/dashboard.component';
import { ParserComponent } from './component/parser/parser.component';
import { MenuComponent } from './component/_core/menu/menu.component';
import { NodeComponent } from './component/_core/menu/node/node.component';
import { NavbarComponent } from './component/_core/navbar/navbar.component';
import { DownloadPanelComponent } from './component/_core/navbar/download-panel/download-panel.component';
import { CookieService } from "ngx-cookie-service";
import { SettingsComponent } from './component/settings/settings.component';
import { ListComponent } from './component/users/list/list.component';
import { GroupsComponent } from './component/users/groups/groups.component';
import { ContentHeaderComponent } from './component/_core/content-header/content-header.component';
import { EditorComponent } from './component/users/list/editor/editor.component';
import { PageLoaderComponent } from "./component/_core/page-loader/page-loader.component";
import { PaginatorComponent } from './component/_core/misc/paginator/paginator.component';
import { NodesListComponent } from './component/parser/nodes-list/nodes-list.component';
import { FilesListComponent } from './component/parser/files-list/files-list.component';
import { SavedNodesComponent } from './component/parser/saved-nodes/saved-nodes.component';
import { ModalComponent } from './component/_core/modal/modal.component';
import {MovieLengthPipe} from "./pipe/movie-length.pipe";
import {TruncateStringPipe} from "./pipe/truncate-string.pipe";

@NgModule({
	declarations: [
		// components
		IndexComponent,
		DashboardComponent,
		ParserComponent,
		MenuComponent,
		NavbarComponent,
		NodeComponent,
		DownloadPanelComponent,
		SettingsComponent,
		ListComponent,
		GroupsComponent,
		ContentHeaderComponent,
		EditorComponent,
		PageLoaderComponent,
		PaginatorComponent,
		NodesListComponent,
		FilesListComponent,
		SavedNodesComponent,
		ModalComponent,

		// pipes
		MovieLengthPipe,
		TruncateStringPipe
	],
	imports: [
		BrowserModule,
		FormsModule,
		AppRoutingModule,
		RouterModule.forRoot(routes),
		HttpClientModule
	],
	providers: [
		CookieService
	],
	bootstrap: [
		IndexComponent
	]
})

export class AppModule {
	title = 'G-App'
}
