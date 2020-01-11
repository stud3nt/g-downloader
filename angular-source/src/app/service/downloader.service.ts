import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { ParsedFile } from "../model/parsed-file";
import { HttpHelper } from "../helper/http-helper";
import { map } from "rxjs/operators";
import {HttpService} from "./http.service";
import {JsonResponse} from "../model/json-response";

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

	public setDownloaderStatus(downloadStatus:string) {
		return this.get(
			this.router.generateUrl('api_downloader_change_status', {'status':status})
		);
	}

	public checkDownloaderStatus() {
		return this.get(
			this.router.generateUrl('api_downloader_check_status')
		);
	}
}
