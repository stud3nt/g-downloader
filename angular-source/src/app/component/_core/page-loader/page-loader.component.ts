import { Component, OnInit } from '@angular/core';
import { PageLoaderDataService } from "../../../service/data/page-loader-data.service";
import { HttpClient } from "@angular/common/http";
import { RouterService } from "../../../service/router.service";
import { PreloaderData } from "../../../model/preloader-data";
import { JsonResponse } from "../../../model/json-response";
import { HttpService } from "../../../service/http.service";

@Component({
  	selector: 'app-page-loader',
  	templateUrl: './page-loader.component.html',
	styleUrls: ['./page-loader.component.scss']
})
export class PageLoaderComponent extends HttpService implements OnInit {

	public preloaderData: PreloaderData = new PreloaderData();

	public refreshDataTimeout = null;
	public statusTimeout = null;

	constructor(
		private pageLoaderDataService: PageLoaderDataService,
		private router: RouterService,
		protected http: HttpClient
	) {
		super(http);
	}

	ngOnInit() {
		// loader data listener service - PreloaderData object
		this.pageLoaderDataService.loaderData.subscribe((preloaderData: PreloaderData) => { // listen loader data changes
			this.preloaderData = preloaderData;
		});

		// progress listener service - number
		this.pageLoaderDataService.loaderProgress.subscribe((progress: number) => {
			this.preloaderData.progress = progress;
		});

		// description listener service - string
		this.pageLoaderDataService.loaderDescription.subscribe((description: string) => {
			this.preloaderData.description = description;
		});

		// loader progress from api (true|false) listener service
		this.pageLoaderDataService.loaderProgressFromApi.subscribe((checkProgressFromApi: boolean) => {
			this.preloaderData.checkProgressFromApi = checkProgressFromApi;

			if (checkProgressFromApi) {
				this.refreshDataFromApi();
			} else if (!this.preloaderData.checkProgressFromApi) {
				this.resetDataInApi();
			}
		});

		// loader status - show/hide listener service
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

		this.get(
			this.router.generateUrl('api_user_operation_progress')
		).subscribe((response: JsonResponse) => {
			if (response.success()) {
				Object.assign(this.preloaderData, response.data);

				if (this.preloaderData.progress > 0 && this.preloaderData.progress < 100)
					this.preloaderData.visible = true;

				clearTimeout(this.refreshDataTimeout);

				if (this.preloaderData.progress < 100) {
					this.refreshDataTimeout = setTimeout(() => {
						this.refreshDataFromApi();
					}, 1000);
				} else {
					this.resetDataInApi();
				}
			} else {

			}
		});
	}

	protected resetDataInApi() {
		clearTimeout(this.statusTimeout);

		this.get(
			this.router.generateUrl('api_user_reset_operation_progress')
		).subscribe((response: JsonResponse) => {
			this.preloaderData.reset();
		});
	}

}
