import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { ParserRequest } from "../model/parser-request";
import { map } from "rxjs/operators";
import { ParserNode } from "../model/parser-node";

@Injectable({
  providedIn: 'root'
})
export class ParserService {

	constructor(
		private http: HttpClient,
		private router: RouterService,
		private auth: AuthService
	) { }

	public executeAction(parserRequest: ParserRequest) {
		let httpParams = this.prepareHttpParams(parserRequest);

		return this.http.post(this.router.generateUrl('api_parsers_action'), httpParams).pipe(
			map((response:Response) => new ParserRequest(response))
		);
	}

	public markNode(object: ParserNode, status: string = null) {
		let httpParams = this.prepareHttpParams(object);

		return this.http.post(
			this.router.generateUrl('api_parsers_mark_node'), httpParams
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
