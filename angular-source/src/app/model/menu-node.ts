// menu node

export class MenuNode {
	route: string = null;
	routeParams: object = [];
	label: string;
	icon: string = null;
	childs?: MenuNode[];
}