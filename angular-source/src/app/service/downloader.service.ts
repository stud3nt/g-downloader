import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { HttpService } from "./http.service";

@Injectable({
  providedIn: 'root'
})
export class DownloaderService extends HttpService {

	constructor(
		protected http: HttpClient,
		protected router: RouterService,
		protected auth: AuthService
	) {
		super(http);
	}

    public getDownladedFilesList() {

    }

	public downloadProcess(downloadedFilesNumber: number = 6) {
		return this.get(this.router.generateUrl('api_downloader_process', {
		    downloadedFileNumber: downloadedFilesNumber
        }));
	}

	public stopDownload() {
		return this.get(
			this.router.generateUrl('api_downloader_stop')
		);
	}
}
