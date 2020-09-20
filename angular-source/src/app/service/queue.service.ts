import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { AuthService } from "./auth.service";
import { HttpService } from "./http.service";
import { QueueRequest } from "../model/request/queue-request";
import { HttpHelper } from "../helper/http-helper";

@Injectable({
  providedIn: 'root'
})
export class QueueService extends HttpService {

	constructor(
		protected http: HttpClient,
		protected router: RouterService,
		protected auth: AuthService
	) {
		super(http);
	}

	public getQueuedFilesPackage(queueRequest: QueueRequest) {
	    let httpParams = HttpHelper.convert(queueRequest);

        return this.post(
            this.router.generateUrl('api_queue_prepare_queue_package'), httpParams
        );
    }
}
