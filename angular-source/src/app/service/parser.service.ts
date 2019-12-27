import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { ParserRequest } from "../model/parser-request";
import { ParserNode } from "../model/parser-node";
import { HttpHelper } from "../helper/http-helper";
import { map } from "rxjs/operators";

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
		let formData = HttpHelper.convertObjectToFormData(parserRequest);

		return this.http.post(this.router.generateUrl('api_parsers_action'), formData).pipe(
			map((response:Response) => new ParserRequest(response))
		);
	}

	public markNode(object: ParserNode, status: string = null) {
		let formData = HttpHelper.convertObjectToFormData(object);

		return this.http.post(
			this.router.generateUrl('api_parsers_mark_node'), formData
		);
	}

}
