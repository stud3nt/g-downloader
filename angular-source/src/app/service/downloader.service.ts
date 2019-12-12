import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { ParsedFile } from "../model/parsed-file";
import {map} from "rxjs/operators";
import {ParserRequest} from "../model/parser-request";

@Injectable({
  providedIn: 'root'
})
export class DownloaderService {

	constructor(
		private http: HttpClient,
		private router: RouterService,
		private auth: AuthService
	) { }

	public toggleFileQueue(file: ParsedFile) {
		let httpParams = this.prepareHttpParams(file);

		return this.http.post(
			this.router.generateUrl('api_file_toggle_queue'), httpParams
		).pipe(
			map((response: Response) => new ParsedFile(response))
		);
	}

	public toggleFilePreview(file: ParsedFile) {
		let httpParams = this.prepareHttpParams(file);

		return this.http.post(
			this.router.generateUrl('api_file_toggle_preview'), httpParams
		).pipe(
			map((response: Response) => new ParsedFile(response))
		);
	}

	private prepareHttpParams(paramObject: any) : FormData {
		let httpParams = new FormData();

		if (Object.keys(paramObject).length > 0) {
			for (let parameterName in paramObject) {
				let parameterValue = paramObject[parameterName];

				if (typeof parameterValue === 'object') {
					parameterValue = JSON.stringify(parameterValue);
				} else if (typeof parameterValue === 'function') {
					continue;
				}

				httpParams.append(parameterName, parameterValue);
			}
		}

		httpParams.append('_token', this.auth.token);

		return httpParams;
	}
}
