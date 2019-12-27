import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { JsonResponse } from "../model/json-response";
import { HttpHelper}  from "../helper/http-helper";
import { map } from "rxjs/operators";

@Injectable({
  providedIn: 'root'
})
export class HttpService {

	constructor(
		protected http: HttpClient
	) { }

	protected get(url: string) {
		return this.http.get(url).pipe(
			map((response:Response) => new JsonResponse(response))
		)
	}

	protected post(url: string, data: any) {
		let httpForm = HttpHelper.convertObjectToFormData(data);

		return this.http.post(url, httpForm).pipe(
			map((response:Response) => new JsonResponse(response))
		)
	}

}
