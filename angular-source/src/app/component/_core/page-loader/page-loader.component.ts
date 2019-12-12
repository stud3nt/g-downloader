import { Component, OnInit } from '@angular/core';
import { PageLoaderDataService } from "../../../service/data/page-loader-data.service";
import { HttpClient } from "@angular/common/http";
import { RouterService } from "../../../service/router.service";
import { ConfigService } from "../../../service/config.service";
import { PreloaderData } from "../../../model/preloader-data";
import { map } from "rxjs/operators";

@Component({
  	selector: 'app-page-loader',
  	templateUrl: './page-loader.component.html',
	styleUrls: ['./page-loader.component.scss']
})
export class PageLoaderComponent implements OnInit {

	public preloaderData: PreloaderData = new PreloaderData();

	public refreshDataTimeout = null;
	public statusTimeout = null;

	constructor(
		private pageLoaderDataService: PageLoaderDataService,
		private router: RouterService,
		private config: ConfigService,
		private http: HttpClient
	) { }

	ngOnInit() {
		// loader data - PreloaderData object
		this.pageLoaderDataService.loaderData.subscribe((preloaderData: PreloaderData) => { // listen loader data changes
			this.preloaderData = preloaderData;
		});

		// progress - number
		this.pageLoaderDataService.loaderProgress.subscribe((progress: number) => {
			this.preloaderData.progress = progress;
		});

		// description - string
		this.pageLoaderDataService.loaderDescription.subscribe((description: string) => {
			this.preloaderData.description = description;
		});

		// loader progress from api (true|false)
		this.pageLoaderDataService.loaderProgressFromApi.subscribe((checkProgressFromApi: boolean) => {
			this.preloaderData.checkProgressFromApi = checkProgressFromApi;

			if (checkProgressFromApi) {
				this.refreshDataFromApi();
			} else {
				this.resetDataInApi();
			}
		});

		// loader status - show/hide
		this.pageLoaderDataService.loaderStatus.subscribe((status: object) => { // listen loader status forcing
			clearTimeout(this.statusTimeout);

			this.statusTimeout = setTimeout(() => {
				switch (status['status']) {
					case 'hide':
						this.preloaderData.visible = false;
						this.preloaderData.reset();
						break;

					case 'show':
						this.preloaderData.visible = true;
						break;
				}
			});
		});
	}

	/**
	 * Makes request and get progress data from API
	 */
	protected refreshDataFromApi() {
		if (!this.preloaderData.checkProgressFromApi) {
			return;
		}

		this.http.get(
			this.router.generateUrl('api_user_operation_progress')
		).pipe(
			map((response:Response) => {
				Object.assign(this.preloaderData, response);
			})
		).subscribe(response => {
			clearTimeout(this.refreshDataTimeout);

			if (this.preloaderData.progress < 100) {
				this.refreshDataTimeout = setTimeout(() => {
					this.refreshDataFromApi();
				}, 1000);
			} else {
				this.resetDataInApi();
			}
		});
	}

	protected resetDataInApi() {
		clearTimeout(this.statusTimeout);

		this.http.get(
			this.router.generateUrl('api_user_reset_operation_progress')
		).subscribe(response => {
			this.preloaderData.reset();
		});
	}

}
