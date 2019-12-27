import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { ParsedFile } from "../model/parsed-file";
import { HttpHelper } from "../helper/http-helper";
import { map } from "rxjs/operators";

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
		let httpParams = HttpHelper.convertObjectToFormData(file);

		return this.http.post(
			this.router.generateUrl('api_file_toggle_queue'), httpParams
		).pipe(
			map((response: Response) => new ParsedFile(response))
		);
	}

	public toggleFilePreview(file: ParsedFile) {
		let httpParams = HttpHelper.convertObjectToFormData(file);

		return this.http.post(
			this.router.generateUrl('api_file_toggle_preview'), httpParams
		).pipe(
			map((response: Response) => new ParsedFile(response))
		);
	}
}
