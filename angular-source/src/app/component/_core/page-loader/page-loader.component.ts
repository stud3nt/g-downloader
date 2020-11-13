import { Component, OnInit } from '@angular/core';
import { PageLoaderDataService } from "../../../service/data/page-loader-data.service";
import { PageLoaderData } from "../../../model/page-loader-data";

@Component({
  	selector: 'app-page-loader',
  	templateUrl: './page-loader.component.html',
	styleUrls: ['./page-loader.component.scss']
})
export class PageLoaderComponent implements OnInit {

    public progress: number = 0;

    public visible: boolean = false;

    public hidingTimeout = null;

	constructor(
		private pageLoaderDataService: PageLoaderDataService
	) {}

	ngOnInit() {
		this.pageLoaderDataService.data.subscribe((data: PageLoaderData) => {
			this.progress = data.progress;
			this.visible = data.visible;

			// hide request or 100% reached;
			if (!data.visible || (this.progress === 100 && data.autoClose)) {
                this.hidingTimeout = setTimeout(() => {
                    this.progress = 0;
                    this.visible = false;
                }, data.autoCloseTimeout);
            }
		});
	}
}
