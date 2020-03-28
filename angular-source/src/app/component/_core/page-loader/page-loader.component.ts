import { Component, OnInit } from '@angular/core';
import { PageLoaderDataService } from "../../../service/data/page-loader-data.service";
import { HttpClient } from "@angular/common/http";
import { PreloaderData } from "../../../model/preloader-data";

@Component({
  	selector: 'app-page-loader',
  	templateUrl: './page-loader.component.html',
	styleUrls: ['./page-loader.component.scss']
})
export class PageLoaderComponent implements OnInit {

	public preloaderData: PreloaderData = new PreloaderData();

	public statusTimeout = null;

	constructor(
		private pageLoaderDataService: PageLoaderDataService,
		protected http: HttpClient
	) {}

	ngOnInit() {
		let title = document.title;

		// loader data listener service - PreloaderData object
		this.pageLoaderDataService.loaderData.subscribe((preloaderData: PreloaderData) => { // listening for loader data changes
			this.preloaderData = preloaderData;
		});

		// progress listener service - number
		this.pageLoaderDataService.loaderProgress.subscribe((progress: number) => {
			this.preloaderData.progress = progress;

			if (progress > 0)
				document.title = '['+progress+'%] '+title;
		});

		// description listener service - string
		this.pageLoaderDataService.loaderDescription.subscribe((description: string) => {
			this.preloaderData.description = description;
		});

		// loader status - show/hide listener service
		this.pageLoaderDataService.loaderStatus.subscribe((status: any) => { // listen forced loader status
			clearTimeout(this.statusTimeout);

			this.statusTimeout = setTimeout(() => {
				switch (status.status) {
					case 'hide':
						this.preloaderData.visible = false;
						this.preloaderData.reset();
						document.title = title;
						break;

					case 'show':
						this.preloaderData.visible = true;
						break;
				}
			}, status.timeout);
		});
	}
}
