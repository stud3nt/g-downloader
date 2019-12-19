import {Component, ViewEncapsulation} from '@angular/core';
import {WindowDataService} from "./service/data/window-data.service";

@Component({
	selector: 'app-root',
	templateUrl: './app.component.html',
	encapsulation: ViewEncapsulation.None
})

export class IndexComponent {
	title = 'G-Downloader';
	name = 'siemanko';
	sidebarClass = 'sidebar-mini';
}
