import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { User } from "../model/user";
import { CookieService } from "ngx-cookie-service";
import { RouterService } from "./router.service";
import { LoginForm } from "../model/form/login-form";
import { HttpService } from "./http.service";

@Injectable({
  providedIn: 'root'
})

export class AuthService extends HttpService
{
	// logged user model
	public user: User = null;

	// is user logged in?
	public isLoggedIn: boolean = false;

	/**
	 * Retrieve user and token from cookies (if exists);
	 *
	 * @param http
	 * @param cookie
	 * @param routerService
	 */
	constructor(
		protected http: HttpClient,
		protected cookie: CookieService,
		protected routerService: RouterService
	) {
		super(http);
		this.checkStatus();
	}

	public checkStatus() {
		return this.get(this.routerService.generateUrl('api_user_status'));
	}

	public login(form: LoginForm) {
		return this.post(this.routerService.generateUrl('api_login_check'), form);
	}

	public logout() {
		return this.http.get(this.routerService.generateUrl('api_logout'));
	}
}
