import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { ParserRequest } from "../model/parser-request";
import { ParsedNode } from "../model/parsed-node";
import { HttpHelper } from "../helper/http-helper";
import { map } from "rxjs/operators";
import {HttpService} from "./http.service";

@Injectable({
	providedIn: 'root'
})
export class ParserService extends HttpService {

	constructor(
		protected http: HttpClient,
		private router: RouterService,
		private auth: AuthService
	) {
	    super(http);
    }

	/**
	 * Parser action request
	 *
	 * @param parserRequest
	 */
	public sendParserActionRequest(parserRequest: ParserRequest) {
		parserRequest.clearParsedData();

		let formData = HttpHelper.convert(parserRequest);

		return this.http.post(this.router.generateUrl('api_parsers_action'), formData).pipe(
			map((response:Response) => new ParserRequest(response))
		);
	}

	/**
	 * Update and save existing node data
	 *
	 * @param node: ParsedNode
	 */
	public updateNode(node: ParsedNode) {
		let formData = HttpHelper.convert(node);

		return this.http.post(this.router.generateUrl('api_node_update'), formData).pipe(
			map((response:Response) => new ParsedNode(response))
		);
	}

}
