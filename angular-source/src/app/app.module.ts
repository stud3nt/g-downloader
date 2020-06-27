import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule } from "@angular/forms";

import { IndexComponent } from './app.component';
import { AppRoutingModule } from './app-routing.module';
import { RouterModule } from "@angular/router";
import { routes } from "./app-routing.module";
import { HttpClientModule, HttpClientXsrfModule } from "@angular/common/http";
import { DashboardComponent } from './component/dashboard/dashboard.component';
import { ParserComponent } from './component/parser/parser.component';
import { MenuComponent } from './component/_core/menu/menu.component';
import { NavbarComponent } from './component/_core/navbar/navbar.component';
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
import { MovieLengthPipe } from "./pipe/movie-length.pipe";
import { TruncateStringPipe } from "./pipe/truncate-string.pipe";
import { LoginComponent } from './component/security/login/login.component';
import { MenuNodeComponent } from "./component/_core/menu/menu-node/menu-node.component";
import { ToastrComponent } from './component/_core/toastr/toastr.component';
import { DownloadMiniPanelComponent } from "./component/_core/navbar/download-mini-panel/download-mini-panel.component";
import { DownloaderComponent } from './component/tools/downloader/downloader.component';
import { ParserToolbarComponent } from "./component/parser/parser-toolbar/parser-toolbar.component";
import { TagsComponent } from './component/lists/tags/tags.component';
import { CategoriesComponent } from './component/lists/categories/categories.component';
import {NodeSettingsComponent} from "./component/parser/parser-toolbar/node-settings/node-settings.component";
import { WebsocketConsoleComponent } from './component/_core/navbar/websocket-console/websocket-console.component';

@NgModule({
	declarations: [
		// components
		IndexComponent,
		DashboardComponent,
		ParserComponent,
		MenuComponent,
		NavbarComponent,
		MenuNodeComponent,
		DownloadMiniPanelComponent,
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
        NodeSettingsComponent,

		// pipes
		MovieLengthPipe,
		TruncateStringPipe,
		LoginComponent,
		ToastrComponent,
		DownloaderComponent,
		ParserToolbarComponent,
		TagsComponent,
		CategoriesComponent,
		WebsocketConsoleComponent
	],
	imports: [
		BrowserModule,
		FormsModule,
		AppRoutingModule,
		RouterModule.forRoot(routes),
		HttpClientModule,
		HttpClientXsrfModule.withOptions({
			cookieName: 'XSRF-TOKEN',
			headerName: 'X-XSRF-TOKEN'
		}),
	],
	providers: [
		CookieService
	],
	bootstrap: [
		IndexComponent
	]
})

export class AppModule {
	title = 'G-Downloader'
}
