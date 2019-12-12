import { Component, OnInit } from '@angular/core';
import { ContentHeaderDataService } from "../../../service/data/content-header-data.service";
import { RouterService } from "../../../service/router.service";

@Component({
  selector: 'app-content-header',
  templateUrl: './content-header.component.html'
})
export class ContentHeaderComponent implements OnInit {

	public headerData = {
		title1: 'Dashboard',
		title2: null,
		breadcrumbs: [{
			route: 'app_index',
			icon: 'fa-dashboard'
		}]
	};

  	constructor(private dataService: ContentHeaderDataService, public router: RouterService) { }

	ngOnInit() {
		this.dataService.headerData.subscribe(headerData => {
			this.headerData = headerData;
		});
	}

}
