import { Component, OnInit } from '@angular/core';
import {ContentHeaderDataService} from "../../service/data/content-header-data.service";

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html'
})
export class SettingsComponent implements OnInit {

  constructor(protected headerData: ContentHeaderDataService) { }

  ngOnInit() {
		this.setHeaderData();
  }

  protected setHeaderData() {
		this.headerData.setElement('title1', 'Settings');
		this.headerData.setElement('title2', 'Application settings & preferences');
		this.headerData.clearBreadcrumbs();
		this.headerData.addBreadcrumb({
			route: 'app_settings',
			label: 'Settings',
			icon: 'fa-tasks'
		});
	}
}

