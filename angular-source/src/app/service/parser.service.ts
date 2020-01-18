import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { ParserRequest } from "../model/parser-request";
import { ParserNode } from "../model/parser-node";
import { HttpHelper } from "../helper/http-helper";
import { map } from "rxjs/operators";
import {NodeStatus} from "../enum/node-status";

@Injectable({
  providedIn: 'root'
})
export class ParserService {

	constructor(
		private http: HttpClient,
		private router: RouterService,
		private auth: AuthService
	) { }

	/**
	 * Parser action request
	 *
	 * @param parserRequest
	 */
	public sendParserActionRequest(parserRequest: ParserRequest) {
		let formData = HttpHelper.convertObjectToFormData(parserRequest);

		return this.http.post(this.router.generateUrl('api_parsers_action'), formData).pipe(
			map((response:Response) => new ParserRequest(response))
		);
	}

	/**
	 * Change node status request
	 *
	 * @param node
	 * @param status
	 */
	public markNode(node: ParserNode, status: string = null) {
		if (status === null || node.hasStatus(NodeStatus.Waiting)) {
			return;
		} else {
			node.addStatus(NodeStatus.Waiting);
		}

		if (node.hasStatus(status)) {
			node.removeStatus(status);
		} else {
			node.addStatus(status);
		}

		let formData = HttpHelper.convertObjectToFormData(node);

		return this.http.post(this.router.generateUrl('api_parsers_mark_node'), formData);
	}

}
