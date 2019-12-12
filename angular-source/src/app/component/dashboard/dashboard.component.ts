import { Component, OnInit } from '@angular/core';
import { ContentHeaderDataService } from "../../service/data/content-header-data.service";

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html'
})
export class DashboardComponent implements OnInit {

	constructor(protected headerData: ContentHeaderDataService) { }

	ngOnInit() {
		this.setHeaderData();
	}

	protected setHeaderData() {
		this.headerData.setElement('title1', 'Dashboard');
		this.headerData.setElement('title2', 'Main app panel');
		this.headerData.addBreadcrumb({
			route: 'app_index',
			label: 'dashboard',
			icon: 'fa-dashboard'
		}, true);
	}
}
