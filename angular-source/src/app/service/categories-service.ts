import { Injectable } from '@angular/core';
import { HttpClient } from "@angular/common/http";
import { RouterService } from "./router.service";
import { HttpHelper } from "../helper/http-helper";
import { Category } from "../model/category";
import { map } from "rxjs/operators";
import { JsonResponse } from "../model/json-response";

@Injectable({
  providedIn: 'root'
})
export class CategoriesService {

	constructor(
		protected http: HttpClient,
		protected router: RouterService
	) {}

	public saveCategory(category: Category) {
		let httpParams = HttpHelper.convert(category);

		return this.http.post(
			this.router.generateUrl('api_categories_create'), httpParams
		).pipe(
			map((response:Response) => new JsonResponse(response))
		);
	}

	public deleteCategory(category: Category) {
		let httpParams = HttpHelper.convert(category);

		return this.http.post(
			this.router.generateUrl('api_categories_delete'), httpParams
		).pipe(
			map((response:Response) => new JsonResponse(response))
		);
	}

	public getCategories() {
		return this.http.get(
			this.router.generateUrl('api_categories_list')
		).pipe(
			map((response:Response) => new JsonResponse(response))
		);
	}

}
