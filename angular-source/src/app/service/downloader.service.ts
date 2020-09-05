import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { HttpService } from "./http.service";
import { QueueSettings } from "../model/queue-settings";
import { HttpHelper } from "../helper/http-helper";

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

	public getQueuedFilesList(queueSettings: QueueSettings) {
	    let httpParams = HttpHelper.convert(queueSettings);

        return this.post(
            this.router.generateUrl('api_downloader_prepare_queue'), httpParams
        );
    }

    public getDownloadedFilesList() {

    }

	public startDownloadProcess() {
		return this.get(
			this.router.generateUrl('api_downloader_start_download')
		);
	}

	public stopDownloadProcess() {
		return this.get(
			this.router.generateUrl('api_downloader_stop_download')
		);
	}
}
