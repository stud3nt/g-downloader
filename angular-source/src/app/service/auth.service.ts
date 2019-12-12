import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { User } from "../model/user";
import { CookieService } from "ngx-cookie-service";
import { RouterService } from "./router.service";

@Injectable({
  providedIn: 'root'
})

export class AuthService
{
	// user token
	token: string = null;

	// logged user model
	user: User = null;

	/**
	 * Retrieve user and token from cookies (if exists);
	 *
	 * @param http
	 * @param cookie
	 * @param router
	 */
	constructor(
		protected http: HttpClient,
		protected cookie: CookieService,
		protected router: RouterService
	) {
		this.getToken();
	}

	getToken() {
		this.http.get(this.router.generateUrl('api_user_auth')).subscribe((response) => {
			if (typeof response['token'] !== 'undefined') {
				this.token = response['token'];
			} else {
				// go to login page
			}
		});
	}

	login() {

	}

	logout() {
		this.token = null;
		this.user = null;
	}
}
